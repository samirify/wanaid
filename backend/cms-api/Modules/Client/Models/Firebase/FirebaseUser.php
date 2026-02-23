<?php

declare(strict_types=1);

namespace Modules\Client\Models\Firebase;


class FirebaseUser
{
    public function __construct(
        private readonly string $id,
        private string $email,
        private string $displayName,
        private bool $emailVerified,
    ) {}

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $emailVerified): void
    {
        $this->emailVerified = $emailVerified;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
