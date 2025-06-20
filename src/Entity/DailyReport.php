<?php

namespace StatisticsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use StatisticsBundle\Repository\DailyReportRepository;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: DailyReportRepository::class)]
#[ORM\Table(name: 'ims_statistics_daily_report', options: ['comment' => '统计日报表'])]
class DailyReport implements PlainArrayInterface, \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 20, options: ['comment' => '报表日期，格式：YYYY-MM-DD'])]
    private string $reportDate;

    #[ORM\OneToMany(targetEntity: DailyMetric::class, mappedBy: 'report', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $metrics;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '额外数据'])]
    private ?array $extraData = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function __construct()
    {
        $this->metrics = new ArrayCollection();
    }

    public function getReportDate(): string
    {
        return $this->reportDate;
    }

    public function setReportDate(string $reportDate): self
    {
        $this->reportDate = $reportDate;
        return $this;
    }

    /**
     * 获取所有指标集合
     * 
     * @return Collection<int, DailyMetric>
     */
    public function getMetrics(): Collection
    {
        return $this->metrics;
    }

    /**
     * 添加指标
     */
    public function addMetric(DailyMetric $metric): self
    {
        if (!$this->metrics->contains($metric)) {
            $this->metrics->add($metric);
            $metric->setReport($this);
        }

        return $this;
    }

    /**
     * 移除指标
     */
    public function removeMetric(DailyMetric $metric): self
    {
        if ($this->metrics->removeElement($metric)) {
            // 设置被移除指标的report为null，避免级联操作问题
            if ($metric->getReport() === $this) {
                // 这里我们不设置null，因为我们设置了级联删除和孤儿移除
            }
        }

        return $this;
    }

    /**
     * 获取指定指标的值
     */
    public function getMetricValue(string $metricId, mixed $default = null): mixed
    {
        foreach ($this->metrics as $metric) {
            if ($metric->getMetricId() === $metricId) {
                return $metric->getValue();
            }
        }

        return $default;
    }

    /**
     * 设置指标值
     */
    public function setMetricValue(string $metricId, string $metricName, mixed $value, ?string $unit = null, ?string $category = null): self
    {
        // 查找是否已存在该指标
        $existingMetric = null;
        foreach ($this->metrics as $metric) {
            if ($metric->getMetricId() === $metricId) {
                $existingMetric = $metric;
                break;
            }
        }

        // 如果不存在，创建新的指标
        if (!(bool) $existingMetric) {
            $existingMetric = new DailyMetric();
            $existingMetric->setMetricId($metricId)
                ->setMetricName($metricName)
                ->setReport($this);
            $this->metrics->add($existingMetric);
        }

        // 设置指标的各项属性
        $existingMetric->setMetricName($metricName);

        if ($unit !== null) {
            $existingMetric->setMetricUnit($unit);
        }

        if ($category !== null) {
            $existingMetric->setCategory($category);
        }

        // 设置值
        $existingMetric->setValue($value);

        return $this;
    }

    /**
     * 判断是否存在指定指标
     */
    public function hasMetric(string $metricId): bool
    {
        return $this->findMetric($metricId) !== null;
    }

    public function findMetric(string $metricId): ?DailyMetric
    {
        foreach ($this->getMetrics() as $metric) {
            if ($metric->getMetricId() === $metricId) {
                return $metric;
            }
        }
        return null;
    }

    /**
     * 批量设置指标值
     *
     * @param array $metrics 格式: ['metric_id' => ['name' => 'name', 'value' => value, 'unit' => 'unit', 'category' => 'category'], ...]
     */
    public function addMetrics(array $metrics): self
    {
        foreach ($metrics as $metricId => $data) {
            if (is_array($data) && isset($data['name']) && isset($data['value'])) {
                $this->setMetricValue(
                    $metricId,
                    $data['name'],
                    $data['value'],
                    $data['unit'] ?? null,
                    $data['category'] ?? null
                );
            } else {
                // 兼容旧格式，只提供值
                $this->setMetricValue($metricId, $metricId, $data);
            }
        }

        return $this;
    }

    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    public function setExtraData(?array $extraData): self
    {
        $this->extraData = $extraData;
        return $this;
    }

    public function retrievePlainArray(): array
    {
        $metricsArray = [];

        foreach ($this->metrics as $metric) {
            $metricsArray[$metric->getMetricId()] = [
                'name' => $metric->getMetricName(),
                'value' => $metric->getValue(),
                'unit' => $metric->getMetricUnit(),
                'category' => $metric->getCategory()
            ];
        }

        return [
            'id' => $this->getId(),
            'reportDate' => $this->getReportDate(),
            'metrics' => $metricsArray,
            'extraData' => $this->getExtraData(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
        ];
    }

    public function __toString(): string
    {
        return sprintf('DailyReport[%s]', $this->reportDate?->format('Y-m-d') ?? 'no-date');
    }
}
