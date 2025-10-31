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
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use StatisticsBundle\Entity\DailyReport;

/**
 * @extends AbstractCrudController<DailyReport>
 */
#[AdminCrud(routePath: '/statistics/daily-report', routeName: 'statistics_daily_report')]
final class DailyReportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DailyReport::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('日报表')
            ->setEntityLabelInPlural('日报表')
            ->setPageTitle('index', '日报表列表')
            ->setPageTitle('detail', fn (DailyReport $report) => sprintf('日报表: %s', $report->getReportDate()))
            ->setPageTitle('edit', fn (DailyReport $report) => sprintf('编辑日报表: %s', $report->getReportDate()))
            ->setPageTitle('new', '新建日报表')
            ->setDefaultSort(['reportDate' => 'DESC'])
            ->setSearchFields(['reportDate'])
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

        yield TextField::new('reportDate', '报表日期')
            ->setHelp('格式：YYYY-MM-DD，如：2023-12-01')
            ->setRequired(true)
        ;

        if (Crud::PAGE_DETAIL === $pageName) {
            yield AssociationField::new('metrics', '指标数据')
                ->hideOnForm()
                ->hideOnIndex()
                ->setFormTypeOption('by_reference', false)
            ;
        }

        if (Crud::PAGE_INDEX === $pageName) {
            yield IntegerField::new('metricsCount', '指标数量')
                ->hideOnForm()
            ;
        }

        if (Crud::PAGE_DETAIL === $pageName || Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            yield CodeEditorField::new('extraData', '额外数据')
                ->setLanguage('javascript')
                ->setHelp('JSON格式的额外数据，可选')
                ->hideOnIndex()
                ->formatValue(function ($value) {
                    return is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value;
                })
                ->setFormTypeOption('data_class', null)
                ->setFormTypeOption('empty_data', null)
                ->setRequired(false)
            ;
        }

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
            ->add(TextFilter::new('reportDate', '报表日期'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
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
            ->leftJoin('entity.metrics', 'metrics')
            ->addSelect('metrics')
        ;
    }
}
