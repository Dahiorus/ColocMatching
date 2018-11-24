<?php

namespace App\Core\Security\User;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Form\Type\Security\LostPasswordForm;
use App\Core\Form\Type\User\PasswordRequestForm;
use App\Core\Manager\Notification\MailManager;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Manager\User\UserTokenDtoManagerInterface;
use App\Core\Validator\FormValidator;
use Psr\Log\LoggerInterface;

class PasswordRequester
{
    private const REQUEST_PASSWORD_MAIL_SUBJECT = "mail.subject.user.password_request";
    private const REQUEST_PASSWORD_MAIL_TEMPLATE = "mail/User/user_password_request_mail.html.twig";

    /** @var LoggerInterface */
    private $logger;

    /** @var UserDtoManagerInterface */
    private $userManager;

    /** @var UserTokenDtoManagerInterface */
    private $userTokenManager;

    /** @var FormValidator */
    private $formValidator;

    /** @var MailManager */
    private $mailManager;


    public function __construct(LoggerInterface $logger, UserDtoManagerInterface $userManager,
        UserTokenDtoManagerInterface $userTokenManager, FormValidator $formValidator, MailManager $mailManager)
    {
        $this->logger = $logger;
        $this->userManager = $userManager;
        $this->userTokenManager = $userTokenManager;
        $this->formValidator = $formValidator;
        $this->mailManager = $mailManager;
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
        $this->logger->debug("Requesting a lost password for [{email}]", $data);

        $this->formValidator->validateForm(null, $data, PasswordRequestForm::class, true);

        $user = $this->userManager->findByUsername($data["email"]);
        $userToken = $this->userTokenManager->create($user, UserToken::LOST_PASSWORD);

        $this->mailManager->sendEmail($user, self::REQUEST_PASSWORD_MAIL_SUBJECT,
            self::REQUEST_PASSWORD_MAIL_TEMPLATE, array (), array (
                "recipient" => $user,
                "token" => $userToken->getToken()
            ));

        $this->logger->info("Password request done for [{user}]", array ("user" => $user, "token" => $userToken));
    }


    /**
     * Updates a user lost password with the request data
     *
     * @param array $data The request data
     *
     * @return UserDto
     * @throws EntityNotFoundException
     * @throws InvalidFormException
     * @throws InvalidParameterException
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

        $this->logger->info("User password updated for [{user}]", array ("user" => $user));

        return $user;
    }

}
