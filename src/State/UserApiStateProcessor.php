<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use App\Repository\UserRepository;

class UserApiStateProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository $userRepository
    )
    {

    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        assert($data instanceof UserApi);

        $user = $this->mapDtoToEntity($data);
        dd($user);
    }

    private function mapDtoToEntity(UserApi $userApi): User
    {
        if ($userApi->id) {
            $user = $this->userRepository->find($userApi->id);

            if (!$user) {
                throw new \Exception(sprintf('User %d not found', $userApi->id));
            }
        } else {
            $user = new User();
        }

        $user->setEmail($userApi->email);
        $user->setUsername($userApi->username);
        $user->setPassword('TODO properly');
        // TODO: handle dragon treasures

        return $user;
    }
}
