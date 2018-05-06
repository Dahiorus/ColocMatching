<?php

namespace ColocMatching\CoreBundle\Service;

use ColocMatching\CoreBundle\DTO\User\UserDto;
use ColocMatching\CoreBundle\Entity\User\UserToken;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Form\Type\Security\LostPasswordForm;
use ColocMatching\CoreBundle\Form\Type\User\PasswordRequestForm;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Manager\User\UserTokenDtoManagerInterface;
use ColocMatching\CoreBundle\Security\User\LostPassword;
use ColocMatching\CoreBundle\Validator\FormValidator;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordRequester
{
    private const REQUEST_PASSWORD_MAIL_SUBJECT = "text.mail.user.password_request.subject";
    private const REQUEST_PASSWORD_MAIL_TEMPLATE = "MailBundle:User:password_request.html.twig";

    /** @var LoggerInterface */
    private $logger;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserTokenDtoManagerInterface */
    private $userTokenManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var MailerService */
    private $mailerService;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager,
        UserTokenDtoManagerInterface $userTokenManager, FormValidator $formValidator, MailerService $mailerService,
        UrlGeneratorInterface $urlGenerator)
    {
        $this->logger = $logger;
        $this->userManager = $userManager;
        $this->userTokenManager = $userTokenManager;
        $this->formValidator = $formValidator;
        $this->mailerService = $mailerService;
        $this->urlGenerator = $urlGenerator;
    }


    /**
     * Creates a user token for the email in the data
     *
     * @param array $data
     *
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws InvalidParameterException
     */
    public function requestPassword(array $data)
    {
        $this->logger->debug("Requesting a lost password", $data);

        $this->formValidator->validateForm(null, $data, PasswordRequestForm::class, true);

        $user = $this->userManager->findByUsername($data["email"]);
        $userToken = $this->userTokenManager->create($user, UserToken::LOST_PASSWORD);

        $this->mailerService->sendMail($user, self::REQUEST_PASSWORD_MAIL_SUBJECT,
            self::REQUEST_PASSWORD_MAIL_TEMPLATE, array (), array (
                "user" => $user,
                "requestUrl" => $this->urlGenerator->generate("coloc_matching.lost_password_url",
                    array ("token" => $userToken->getToken()), UrlGeneratorInterface::ABSOLUTE_URL)
            ));

        $this->logger->debug("Password request done", array ("user" => $user, "token" => $userToken));
    }


    /**
     * Creates a user token for the email in the data
     *
     * @param array $data
     *
     * @return UserDto
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws ORMException
     */
    public function updatePassword(array $data) : UserDto
    {
        $this->logger->debug("Updating a user password");

        /** @var LostPassword $lostPassword */
        $lostPassword = $this->formValidator->validateForm(new LostPassword(), $data, LostPasswordForm::class, true);

        $userToken = $this->userTokenManager->findByToken($lostPassword->getToken(), UserToken::LOST_PASSWORD);

        $user = $this->userManager->findByUsername($userToken->getUsername());
        $user = $this->userManager->update($user, array ("plainPassword" => $lostPassword->getNewPassword()), false);
        
        $this->userTokenManager->delete($userToken);

        $this->logger->debug("User password updated", array ("user" => $user));

        return $user;
    }

}
