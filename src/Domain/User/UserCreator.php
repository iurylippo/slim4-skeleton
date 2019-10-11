<?php

namespace App\Domain\User;

use App\Domain\Service\DomainServiceInterface;
use App\Domain\User\Data\UserData;
use App\Domain\User\Repository\UserCreatorRepository;
use App\Factory\LoggerFactory;
use Odan\Validation\ValidationException;
use Odan\Validation\ValidationResult;
use Psr\Log\LoggerInterface;

/**
 * Domain Service.
 */
final class UserCreator implements DomainServiceInterface
{
    /**
     * @var UserCreatorRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The constructor.
     *
     * @param UserCreatorRepository $repository The repository
     * @param LoggerFactory $loggerFactory The logger factory
     */
    public function __construct(UserCreatorRepository $repository, LoggerFactory $loggerFactory)
    {
        $this->repository = $repository;
        $this->logger = $loggerFactory
            ->addFileHandler('user_creator.log')
            ->createInstance('user_creator');
    }

    /**
     * Create a new user.
     *
     * @param UserData $user The user data
     *
     * @throws ValidationException
     *
     * @return int The new user ID
     */
    public function createUser(UserData $user): int
    {
        // Validation
        $validation = $this->validateUser($user);

        if ($validation->isFailed()) {
            $validation->setMessage(__('Please check your input'));

            throw new ValidationException($validation);
        }

        // Insert user
        $userId = $this->repository->insertUser($user);

        // Logging
        $this->logger->info(__('User created successfully: %s', $userId));

        return $userId;
    }

    /**
     * @param UserData $user The user
     *
     * @return ValidationResult
     */
    private function validateUser(UserData $user): ValidationResult
    {
        $validation = new ValidationResult();

        if (empty($user->userName)) {
            $validation->addError('username', __('Input required'));
        }

        if (empty($user->email)) {
            $validation->addError('email', __('Input required'));
        }

        return $validation;
    }
}
