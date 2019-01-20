<?php

namespace App\Core\Security\User;

use App\Core\DTO\User\UserDto;
use App\Core\DTO\User\UserTokenDto;
use App\Core\Entity\User\UserToken;
use App\Core\Exception\EntityNotFoundException;
use App\Core\Exception\ExpiredUserTokenException;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Form\Type\Security\LostPasswordForm;
use App\Core\Form\Type\User\PasswordRequestForm;
use App\Core\Form\Type\User\UserDtoForm;
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
     */
    public function requestPassword(array $data)
    {
        $this->logger->debug("Requesting a lost password for [{email}]", $data);

        $this->formValidator->validateForm(null, $data, PasswordRequestForm::class, true);
        /** @var UserDto $user */
        $user = $this->userManager->findByUsername($data["email"]);
        $reason = UserToken::LOST_PASSWORD;

        try
        {
            $userToken = $this->userTokenManager->createOrUpdate($user, $reason, new \DateTime("tomorrow"));
        }
        catch (\Exception $e)
        {
            $this->logger->critical("Unexpected error while creating a [{reason}] user token for [{user}]",
                array ("reason" => $reason, "user" => $user, "exception" => $e));

            return;
        }

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
     * @throws ExpiredUserTokenException
     */
    public function updatePassword(array $data) : UserDto
    {
        $this->logger->debug("Updating a user password");

        /** @var LostPassword $lostPassword */
        $lostPassword = $this->formValidator->validateForm(new LostPassword(), $data, LostPasswordForm::class, true);
        $userToken = $this->userTokenManager->getByToken($lostPassword->getToken(), UserToken::LOST_PASSWORD);

        if ($this->tokenIsExpired($userToken))
        {
            throw new ExpiredUserTokenException($userToken);
        }

        $user = $this->userManager->findByUsername($userToken->getUsername());
        $user = $this->userManager->update(
            $user, ["plainPassword" => $lostPassword->getNewPassword()], false, UserDtoForm::class, false);
        $this->userTokenManager->delete($userToken);

        $this->logger->info("User password updated for [{user}]", array ("user" => $user));

        return $user;
    }


    /**
     * Tests if the given user token is expired
     *
     * @param UserTokenDto $token The user token to test
     *
     * @return bool
     */
    private function tokenIsExpired(UserTokenDto $token) : bool
    {
        try
        {
            return $token->isExpired();
        }
        catch (\Exception $e)
        {
            $this->logger->critical("Unexpected error while testing if the user token [{token}] is expired",
                array ("token" => $token, "exception" => $e));

            throw new \RuntimeException("Unexpected error on the user token [$token] processing", 500, $e);
        }
    }

}
