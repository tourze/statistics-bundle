<?php

namespace StatisticsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use StatisticsBundle\Repository\DailyReportRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\PlainArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

/**
 * @implements PlainArrayInterface<string, mixed>
 */
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
    #[Assert\NotBlank(message: '报表日期不能为空')]
    #[Assert\Length(max: 20, maxMessage: '报表日期长度不能超过 {{ limit }} 个字符')]
    #[Assert\Regex(pattern: '/^\d{4}-\d{2}-\d{2}$/', message: '报表日期格式必须为 YYYY-MM-DD')]
    private string $reportDate;

    /** @var Collection<int, DailyMetric> */
    #[ORM\OneToMany(targetEntity: DailyMetric::class, mappedBy: 'report', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $metrics;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '额外数据'])]
    #[Assert\Type(type: 'array', message: '额外数据必须是数组类型')]
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

    public function setReportDate(string $reportDate): void
    {
        $this->reportDate = $reportDate;
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
     * 获取指标数量
     */
    public function getMetricsCount(): int
    {
        return $this->metrics->count();
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
    public function setMetricValue(string $metricId, string $metricName, mixed $value, ?string $unit = null, ?string $category = null): void
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
            $existingMetric->setMetricId($metricId);
            $existingMetric->setMetricName($metricName);
            $existingMetric->setReport($this);
            $this->metrics->add($existingMetric);
        }

        // 设置指标的各项属性
        $existingMetric->setMetricName($metricName);

        if (null !== $unit) {
            $existingMetric->setMetricUnit($unit);
        }

        if (null !== $category) {
            $existingMetric->setCategory($category);
        }

        // 设置值
        $existingMetric->setValue($value);
    }

    /**
     * 判断是否存在指定指标
     */
    public function hasMetric(string $metricId): bool
    {
        return null !== $this->findMetric($metricId);
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
     * @param array<string, array<string, mixed>|mixed> $metrics 格式: ['metric_id' => ['name' => 'name', 'value' => value, 'unit' => 'unit', 'category' => 'category'], ...]
     */
    public function addMetrics(array $metrics): self
    {
        foreach ($metrics as $metricId => $data) {
            $this->addSingleMetric($metricId, $data);
        }

        return $this;
    }

    /**
     * 添加单个指标
     */
    private function addSingleMetric(string $metricId, mixed $data): void
    {
        if ($this->isStructuredMetricData($data)) {
            $this->addStructuredMetric($metricId, $data);
        } else {
            $this->addLegacyMetric($metricId, $data);
        }
    }

    /**
     * 检查数据是否为结构化指标数据
     *
     * @param mixed $data
     * @phpstan-assert-if-true array<string, mixed> $data
     */
    private function isStructuredMetricData(mixed $data): bool
    {
        return is_array($data) && isset($data['name'], $data['value']);
    }

    /**
     * 添加结构化指标
     *
     * @param array<string, mixed> $data
     */
    private function addStructuredMetric(string $metricId, array $data): void
    {
        $name = $data['name'];
        if (is_string($name)) {
            // Name is already a string, use as-is
        } elseif (null === $name) {
            $name = $metricId;
        } elseif (is_scalar($name)) {
            // Convert scalar values to string
            $name = (string) $name;
        } else {
            // For non-scalar values, use metricId as fallback
            $name = $metricId;
        }

        $unit = null;
        if (array_key_exists('unit', $data)) {
            $unitValue = $data['unit'];
            if (is_string($unitValue) || null === $unitValue) {
                $unit = $unitValue;
            }
        }

        $category = null;
        if (array_key_exists('category', $data)) {
            $categoryValue = $data['category'];
            if (is_string($categoryValue) || null === $categoryValue) {
                $category = $categoryValue;
            }
        }

        $this->setMetricValue(
            $metricId,
            $name,
            $data['value'],
            $unit,
            $category
        );
    }

    /**
     * 添加旧格式指标
     */
    private function addLegacyMetric(string $metricId, mixed $data): void
    {
        $this->setMetricValue($metricId, $metricId, $data);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    /**
     * @param array<string, mixed>|null $extraData
     */
    public function setExtraData(?array $extraData): void
    {
        $this->extraData = $extraData;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrievePlainArray(): array
    {
        $metricsArray = [];

        foreach ($this->metrics as $metric) {
            $metricsArray[$metric->getMetricId()] = [
                'name' => $metric->getMetricName(),
                'value' => $metric->getValue(),
                'unit' => $metric->getMetricUnit(),
                'category' => $metric->getCategory(),
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
        return sprintf('DailyReport[%s]', $this->reportDate ?? 'no-date');
    }
}
