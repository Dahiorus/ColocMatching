<?php

namespace ColocMatching\CoreBundle\Command;

use ColocMatching\CoreBundle\Entity\User\UserConstants;
use ColocMatching\CoreBundle\Exception\EntityNotFoundException;
use ColocMatching\CoreBundle\Exception\InvalidFormException;
use ColocMatching\CoreBundle\Exception\InvalidParameterException;
use ColocMatching\CoreBundle\Manager\User\UserDtoManagerInterface;
use ColocMatching\CoreBundle\Validator\ValidationError;
use Doctrine\ORM\ORMException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Command line to create a user with the role ADMIN
 *
 * @author Dahiorus
 */
class CreateAdminCommand extends Command
{
    protected static $defaultName = "app:create-admin";

    /** @var UserDtoManagerInterface */
    private $userManager;


    public function __construct(UserDtoManagerInterface $userManager)
    {
        parent::__construct();

        $this->userManager = $userManager;
    }


    protected function configure()
    {
        $this->setName(static::$defaultName)->setDescription("Creates a new user with the role ADMIN");
        $this
            ->addArgument("email", InputArgument::REQUIRED, "The admin email that will be used as the username")
            ->addArgument("password", InputArgument::REQUIRED, "The admin plain password")
            ->addArgument("firstName", InputArgument::OPTIONAL, "The admin first name", "Admin")
            ->addArgument("lastName", InputArgument::OPTIONAL, "The admin last name", "Admin");
        $this
            ->addOption("super-admin", null, InputOption::VALUE_NONE, "Set the admin as super admin")
            ->addOption("enabled", null, InputOption::VALUE_NONE, "Enable the admin");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Creating an admin user...");

        try
        {
            $user = $this->userManager->create($this->getFormData($input));
            $user = $this->userManager->addRole($user, "ROLE_ADMIN");

            $enabled = $input->getOption("enabled");

            if ($enabled)
            {
                $output->writeln("Enabling the admin...");
                $user = $this->userManager->updateStatus($user, UserConstants::STATUS_ENABLED);
            }

            $isSuperAdmin = $input->getOption("super-admin");

            if ($isSuperAdmin)
            {
                $output->writeln("Adding the role 'super_admin' to the user");
                $this->userManager->addRole($user, "ROLE_SUPER_ADMIN");
            }

            $output->writeln("Admin user '" . $user->getUsername() . "' created");
        }
        catch (EntityNotFoundException | ORMException | InvalidFormException | InvalidParameterException $e)
        {
            $output->writeln($e->getMessage());

            if ($e instanceof InvalidFormException)
            {
                $errors = $e->getErrors();
                array_walk($errors, function (ValidationError $error) use ($output) {
                    $output->writeln($error);
                });
            }
        }
    }


    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = array ();

        if (!$input->getArgument("email"))
        {
            $question = new Question("Choose an e-mail address for the admin user:");
            $question->setValidator(function ($email) {
                if (empty($email))
                {
                    throw new \Exception("The e-mail address cannot be empty");
                }

                return $email;
            });
            $questions["email"] = $question;
        }

        if (!$input->getArgument("password"))
        {
            $question = new Question("Choose a password for the admin user (min length: 8):");
            $question->setValidator(function ($password) {
                if (empty($password))
                {
                    throw new \Exception("The password cannot be empty");
                }

                return $password;
            });
            $questions["password"] = $question;
        }

        foreach ($questions as $name => $question)
        {
            $answer = $this->getHelper("question")->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }


    /**
     * Gets the data from the input arguments
     *
     * @param InputInterface $input The command input
     *
     * @return string[]
     */
    private function getFormData(InputInterface $input) : array
    {
        return array (
            "email" => $input->getArgument("email"),
            "plainPassword" => $input->getArgument("password"),
            "firstName" => $input->getArgument("firstName"),
            "lastName" => $input->getArgument("lastName"),
            "type" => UserConstants::TYPE_SEARCH
        );
    }

}