<?php

declare(strict_types=1);

namespace StatisticsBundle\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use StatisticsBundle\Entity\DailyMetric;

/**
 * @extends AbstractCrudController<DailyMetric>
 */
#[AdminCrud(routePath: '/statistics/daily-metric', routeName: 'statistics_daily_metric')]
final class DailyMetricCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DailyMetric::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('指标')
            ->setEntityLabelInPlural('指标')
            ->setPageTitle('index', '指标列表')
            ->setPageTitle('detail', fn (DailyMetric $metric) => sprintf('指标: %s', $metric->getMetricName()))
            ->setPageTitle('edit', fn (DailyMetric $metric) => sprintf('编辑指标: %s', $metric->getMetricName()))
            ->setPageTitle('new', '新建指标')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['metricId', 'metricName', 'category'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('report', '所属报表')
            ->setFormTypeOption('choice_label', 'reportDate')
            ->setRequired(true)
        ;

        yield TextField::new('metricId', '指标ID')
            ->setHelp('唯一标识此指标的ID')
            ->setRequired(true)
        ;

        yield TextField::new('metricName', '指标名称')
            ->setHelp('指标的显示名称')
            ->setRequired(true)
        ;

        yield TextField::new('metricUnit', '指标单位')
            ->setHelp('指标的计量单位，如：个、次、MB等')
            ->setRequired(false)
        ;

        yield TextField::new('category', '指标分类')
            ->setHelp('指标的业务分类')
            ->setRequired(false)
        ;

        yield NumberField::new('value', '指标值')
            ->setHelp('指标的数值')
            ->setNumDecimals(2)
            ->setRequired(true)
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('metricId', '指标ID'))
            ->add(TextFilter::new('metricName', '指标名称'))
            ->add(TextFilter::new('category', '指标分类'))
            ->add(EntityFilter::new('report', '所属报表'))
            ->add(NumericFilter::new('value', '指标值'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.report', 'report')
            ->addSelect('report')
        ;
    }
}
