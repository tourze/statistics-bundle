<?php

namespace StatisticsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use StatisticsBundle\Repository\DailyMetricRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: DailyMetricRepository::class)]
#[ORM\Table(name: 'ims_statistics_daily_metric', options: ['comment' => '统计日报指标值'])]
#[ORM\Index(name: 'ims_statistics_daily_metric_report_metric', columns: ['report_id', 'metric_id'])]
class DailyMetric implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: DailyReport::class, inversedBy: 'metrics')]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private DailyReport $report;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '指标ID'])]
    #[Assert\NotBlank(message: '指标ID不能为空')]
    #[Assert\Length(max: 50, maxMessage: '指标ID长度不能超过 {{ limit }} 个字符')]
    private string $metricId;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '指标名称'])]
    #[Assert\NotBlank(message: '指标名称不能为空')]
    #[Assert\Length(max: 50, maxMessage: '指标名称长度不能超过 {{ limit }} 个字符')]
    private string $metricName;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '指标单位'])]
    #[Assert\Length(max: 50, maxMessage: '指标单位长度不能超过 {{ limit }} 个字符')]
    private ?string $metricUnit = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '指标分类'])]
    #[Assert\Length(max: 50, maxMessage: '指标分类长度不能超过 {{ limit }} 个字符')]
    private ?string $category = null;

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => '指标值'])]
    #[Assert\NotNull(message: '指标值不能为空')]
    private float $value = 0.0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getReport(): DailyReport
    {
        return $this->report;
    }

    public function setReport(DailyReport $report): void
    {
        $this->report = $report;
    }

    public function getMetricId(): string
    {
        return $this->metricId;
    }

    public function setMetricId(string $metricId): void
    {
        $this->metricId = $metricId;
    }

    public function getMetricName(): string
    {
        return $this->metricName;
    }

    public function setMetricName(string $metricName): void
    {
        $this->metricName = $metricName;
    }

    public function getMetricUnit(): ?string
    {
        return $this->metricUnit;
    }

    public function setMetricUnit(?string $metricUnit): void
    {
        $this->metricUnit = $metricUnit;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        // 转换为浮点数
        if (is_numeric($value)) {
            $this->value = (float) $value;
        } elseif (is_bool($value)) {
            $this->value = $value ? 1.0 : 0.0;
        } elseif (is_array($value) || is_object($value)) {
            $this->value = 1.0; // 复杂类型默认为1
        } elseif (null === $value) {
            $this->value = 0.0;
        } else {
            // 尝试将字符串转换为数字，如果转换失败则使用0.0
            $numericValue = is_string($value) ? (float) $value : 0.0;
            $this->value = $numericValue;
        }
    }

    public function __toString(): string
    {
        return sprintf('DailyMetric[%s:%s]', $this->metricId ?? 'unknown', $this->value);
    }
}
