<?php

namespace StatisticsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use StatisticsBundle\Repository\DailyMetricRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;

#[ORM\Entity(repositoryClass: DailyMetricRepository::class)]
#[ORM\Table(name: 'ims_statistics_daily_metric', options: ['comment' => '统计日报指标值'])]
#[ORM\Index(name: 'ims_statistics_daily_metric_report_metric', columns: ['report_id', 'metric_id'])]
#[ORM\Index(name: 'ims_statistics_daily_metric_metric_id', columns: ['metric_id'])]
class DailyMetric
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[Groups(['restful_read', 'api_tree', 'admin_curd', 'api_list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: DailyReport::class, inversedBy: 'metrics')]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private DailyReport $report;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '指标ID'])]
    private string $metricId;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '指标名称'])]
    private string $metricName;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '指标单位'])]
    private ?string $metricUnit = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '指标分类'])]
    private ?string $category = null;

    #[ORM\Column(type: Types::FLOAT, options: ['comment' => '指标值'])]
    private float $value = 0.0;

    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[Groups(['restful_read', 'admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Groups(['restful_read', 'admin_curd', 'restful_read'])]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

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

    public function setReport(DailyReport $report): self
    {
        $this->report = $report;
        return $this;
    }

    public function getMetricId(): string
    {
        return $this->metricId;
    }

    public function setMetricId(string $metricId): self
    {
        $this->metricId = $metricId;
        return $this;
    }

    public function getMetricName(): string
    {
        return $this->metricName;
    }

    public function setMetricName(string $metricName): self
    {
        $this->metricName = $metricName;
        return $this;
    }

    public function getMetricUnit(): ?string
    {
        return $this->metricUnit;
    }

    public function setMetricUnit(?string $metricUnit): self
    {
        $this->metricUnit = $metricUnit;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(mixed $value): self
    {
        // 转换为浮点数
        if (is_numeric($value)) {
            $this->value = (float)$value;
        } elseif (is_bool($value)) {
            $this->value = $value ? 1.0 : 0.0;
        } elseif (is_array($value) || is_object($value)) {
            $this->value = 1.0; // 复杂类型默认为1
        } elseif ($value === null) {
            $this->value = 0.0;
        } else {
            // 尝试将字符串转换为数字
            $numericValue = (float)$value;
            $this->value = $numericValue;
        }

        return $this;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }
}
