<?php // Name : Rodain Gouzlan Id:

namespace App\Exceptions;

use RuntimeException;

class StoreDeletionBlockedException extends RuntimeException
{
    /**
     * @var array<int, string>
     */
    private array $blockers;

    /**
     * @param array<int, string> $blockers
     */
    public function __construct(array $blockers, string $message)
    {
        parent::__construct($message);
        $this->blockers = $blockers;
    }

    /**
     * @return array<int, string>
     */
    public function blockers(): array
    {
        return $this->blockers;
    }

    /**
     * @param array<int, string> $blockers
     */
    public static function forBlockers(array $blockers): self
    {
        $label = implode(' and ', $blockers);
        $message = 'This branch cannot be deleted because it is linked to '.$label.'. Please unlink them first and try again.';

        return new self($blockers, $message);
    }
}