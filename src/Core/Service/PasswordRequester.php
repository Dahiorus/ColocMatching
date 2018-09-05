<?php

namespace App\Core\Service;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Form\Type\Security\LostPasswordForm;
use App\Core\Form\Type\User\PasswordRequestForm;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Core\Security\User\LostPassword;
use App\Core\Validator\FormValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordRequester
{
    private const REQUEST_PASSWORD_MAIL_SUBJECT = "mail.user.password_request.subject";
    private const REQUEST_PASSWORD_MAIL_TEMPLATE = "mail/User/password_request.html.twig";

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
     * @param array $data The request data
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

        $this->mailerService->sendEmail($user, self::REQUEST_PASSWORD_MAIL_SUBJECT,
            self::REQUEST_PASSWORD_MAIL_TEMPLATE, array (), array (
                "user" => $user,
                "requestUrl" => $this->urlGenerator->generate("coloc_matching.lost_password_url",
                    array ("token" => $userToken->getToken()), UrlGeneratorInterface::ABSOLUTE_URL)
            ));

        $this->logger->info("Password request done", array ("user" => $user, "token" => $userToken));
    }


    /**
     * Updates a user lost password with the request data
     *
     * @param array $data The request data
     *
     * @return UserDto
     * @throws EntityNotFoundException
     * @throws InvalidFormException
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

        $this->logger->info("User password updated", array ("user" => $user));

        return $user;
    }

}
