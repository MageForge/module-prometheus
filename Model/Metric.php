<?php

declare(strict_types=1);

namespace MageForge\Prometheus\Model;

use Magento\Framework\Serialize\SerializerInterface;

class Metric
{
    /** @var string */
    public const TYPE_GAUGE = 'gauge';

    /** @var string */
    public const TYPE_COUNTER = 'counter';

    protected string $name;
    protected string $type;
    protected array $labels = [];
    protected int|float $value;

    public function __construct(
        string $name,
        int|float $value,
        array $labels = [],
        string $type = Metric::TYPE_COUNTER
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->labels = $labels;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }

    public function getValue(): float|int
    {
        return $this->value;
    }

    public function setValue(int|float $value): void
    {
        $this->value = $value;
    }
}
