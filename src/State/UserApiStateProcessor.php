<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use App\Repository\UserRepository;
use Jane\Component\AutoMapper\AutoMapperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class UserApiStateProcessor implements ProcessorInterface
{
    public function __construct(
        private UserRepository $userRepository,
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $persistProcessor,
        #[Autowire(service: RemoveProcessor::class)] private ProcessorInterface $removeProcessor,
        private AutoMapperInterface $autoMapper
    )
    {

    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        dd($operation);

        assert($data instanceof UserApi);

        $user = $this->mapDtoToEntity($data);

        if ($operation instanceof DeleteOperationInterface) {
            $this->removeProcessor->process($user, $operation, $uriVariables, $context);

            return null;
        }

        $this->persistProcessor->process($user, $operation, $uriVariables, $context);
        $data->id = $user->getId();

        return $data;
    }

    private function mapDtoToEntity(UserApi $userApi): User
    {
        if (isset($userApi->id)) {
            $user = $this->userRepository->find($userApi->id);

            if (!$user) {
                throw new \Exception(sprintf('User %d not found', $userApi->id));
            }
        } else {
            $user = new User();
        }

        return $this->autoMapper->map($userApi, $user);
    }
}
