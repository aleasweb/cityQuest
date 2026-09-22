<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Service;

use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\User\Application\DTO\UpdateProfileRequest;
use App\User\Application\Service\ProfileService;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ProfileServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private UserQuestProgressRepositoryInterface $userProgressRepository;
    private QuestRepositoryInterface $questRepository;
    private ProfileService $profileService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userProgressRepository = $this->createMock(UserQuestProgressRepositoryInterface::class);
        $this->questRepository = $this->createMock(QuestRepositoryInterface::class);
        $this->profileService = new ProfileService(
            $this->userRepository,
            $this->userProgressRepository,
            $this->questRepository
        );
    }

    public function testGetProfileReturnsUserData(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setUsername('testuser');

        $profile = $this->profileService->getProfile($user);

        $this->assertIsArray($profile);
        $this->assertArrayHasKey('id', $profile);
        $this->assertArrayHasKey('email', $profile);
        $this->assertArrayHasKey('username', $profile);
        $this->assertArrayHasKey('createdAt', $profile);
        $this->assertEquals('test@example.com', $profile['email']);
        $this->assertEquals('testuser', $profile['username']);
    }

    public function testGetPublicProfileReturnsPublicDataWithoutEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setUsername('testuser');

        $this->userRepository
            ->expects($this->once())
            ->method('findByUsername')
            ->with('testuser')
            ->willReturn($user);

        $profile = $this->profileService->getPublicProfile('testuser');

        $this->assertIsArray($profile);
        $this->assertArrayHasKey('id', $profile);
        $this->assertArrayHasKey('username', $profile);
        $this->assertArrayHasKey('createdAt', $profile);
        $this->assertArrayNotHasKey('email', $profile); // Email не должен быть в публичном профиле
        $this->assertEquals('testuser', $profile['username']);
    }

    public function testGetPublicProfileThrowsExceptionForNonExistentUser(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('findByUsername')
            ->with('nonexistent')
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        $this->profileService->getPublicProfile('nonexistent');
    }

    public function testUpdateProfileChangesEmail(): void
    {
        $user = new User();
        $user->setEmail('old@example.com');
        $user->setUsername('testuser');

        $this->userRepository
            ->expects($this->once())
            ->method('emailExists')
            ->with('new@example.com')
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $request = new UpdateProfileRequest(email: 'new@example.com');
        $updatedUser = $this->profileService->updateProfile($user, $request);

        $this->assertEquals('new@example.com', $updatedUser->getEmail());
    }

    public function testUpdateProfileThrowsExceptionForDuplicateEmail(): void
    {
        $user = new User();
        $user->setEmail('old@example.com');
        $user->setUsername('testuser');

        $this->userRepository
            ->expects($this->once())
            ->method('emailExists')
            ->with('duplicate@example.com')
            ->willReturn(true);

        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $request = new UpdateProfileRequest(email: 'duplicate@example.com');

        $this->expectException(UserAlreadyExistsException::class);

        $this->profileService->updateProfile($user, $request);
    }

    public function testUpdateProfileWithSameEmailDoesNotThrowException(): void
    {
        $user = new User();
        $user->setEmail('same@example.com');
        $user->setUsername('testuser');

        // Не должно быть вызова emailExists, так как email не изменился
        $this->userRepository
            ->expects($this->never())
            ->method('emailExists');

        // Не должно быть сохранения, так как ничего не изменилось
        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $request = new UpdateProfileRequest(email: 'same@example.com');
        $updatedUser = $this->profileService->updateProfile($user, $request);

        $this->assertEquals('same@example.com', $updatedUser->getEmail());
    }
}
