<?php

namespace App\Command;

use App\Core\DTO\User\UserDto;
use App\Core\Entity\User\UserStatus;
use App\Core\Exception\InvalidFormException;
use App\Core\Exception\InvalidParameterException;
use App\Core\Form\Type\User\AdminUserDtoForm;
use App\Core\Manager\User\UserDtoManagerInterface;
use App\Core\Validator\FormValidator;
use App\Core\Validator\ValidationError;
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

    /** @var FormValidator */
    private $formValidator;


    public function __construct(UserDtoManagerInterface $userManager, FormValidator $formValidator)
    {
        parent::__construct();

        $this->userManager = $userManager;
        $this->formValidator = $formValidator;
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
            ->addOption("enabled", null, InputOption::VALUE_NONE, "Enable the admin")
            ->addOption("dry-run", null, InputOption::VALUE_NONE, "Execute in simulation mode");;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Creating an admin user...");

        try
        {
            /** @var array $data */
            $data = $this->getFormData($input);
            $data["roles"] = array ("ROLE_ADMIN");

            if ($input->getOption("enabled") == true)
            {
                $output->writeln("Enabling the admin...");
                $data["status"] = UserStatus::ENABLED;
            }

            if ($input->getOption("super-admin") == true)
            {
                $output->writeln("Adding the role 'super_admin' to the user");
                $data["roles"][] = "ROLE_SUPER_ADMIN";
            }

            if ($input->getOption("dry-run") == true)
            {
                $user = $this->formValidator->validateDtoForm(new UserDto(), $data, AdminUserDtoForm::class, true);
                $output->writeln("Admin user [$user] should be created", OutputInterface::VERBOSITY_VERBOSE);
            }
            else
            {
                $user = $this->userManager->create($data, AdminUserDtoForm::class);
                $output->writeln("Admin user '" . $user->getUsername() . "' created");
            }

            return 0;
        }
        catch (InvalidFormException | InvalidParameterException $e)
        {
            $output->writeln($e->getMessage());

            if ($e instanceof InvalidFormException)
            {
                $errors = $e->getErrors();
                array_walk($errors, function (ValidationError $error) use ($output) {
                    $output->writeln($error);
                });
            }

            return 1;
        }
    }


    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = array ();

        if (!$input->getArgument("email"))
        {
            $question = new Question("Choose an e-mail address for the admin user: ");
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
            $question = new Question("Choose a password for the admin user (min length: 8): ");
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
        );
    }

}