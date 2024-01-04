<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Type;

use App\Entity\Activity;
use App\Form\Helper\ActivityHelper;
use App\Form\Helper\ProjectHelper;
use App\Repository\ActivityRepository;
use App\Repository\Query\ActivityFormTypeQuery;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom form field type to select an activity.
 */
final class ActivityType extends AbstractType
{
    public function __construct(private ActivityHelper $activityHelper, private ProjectHelper $projectHelper)
    {
    }

    public function getChoiceLabel(Activity $activity): string
    {
        return $this->activityHelper->getChoiceLabel($activity);
    }

    public function groupBy(Activity $activity, $key, $index): ?string
    {
        if (null === $activity->getProject()) {
            return null;
        }

        return $this->projectHelper->getChoiceLabel($activity->getProject());
    }

    /**
     * @param Activity $activity
     * @param string $key
     * @param mixed $value
     * @return array<string, string|int|null>
     */
    public function getChoiceAttributes(Activity $activity, $key, $value): array
    {
        if (null !== ($project = $activity->getProject())) {
            return [
                'data-project' => $project->getId(), 
                'data-currency' => $project->getCustomer()?->getCurrency(),
                'data-id' => $activity->getId()
        ];
        }

        return [];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // documentation is for NelmioApiDocBundle
            'documentation' => [
                'type' => 'integer',
                'description' => 'Activity ID',
            ],
            'label' => 'activity',
            'class' => Activity::class,
            'choice_label' => 'name',
            'choice_attr' => [$this, 'getChoiceAttributes'],
            'choice_label' => [$this, 'getChoiceLabel'],
            'choice_attr' => [$this, 'getChoiceAttributes'],
            'group_by' => [$this, 'groupBy'],
            'query_builder_for_user' => true,
            'projects' => null,
            'activities' => null,
            'project_id' => null,
            'activity_id' => null,
            'activity_name' => null,
            // @var Activity|null
            'ignore_activity' => null,
            'allow_create' => false,
            'attr' => array('style' => 'width: 180px')
        ]);

        $resolver->setDefault('api_data', function (Options $options) {
            if (false !== $options['allow_create']) {
                return ['create' => 'post_activity'];
            }

            return [];
        });

        $resolver->setDefault('query_builder', function (Options $options) {
            
            return function (EntityRepository $er) use ($options) {
                $qb = $er->createQueryBuilder('a')
                ->where('a.id = ?1')
                ->andWhere('a.project = ?2')
                ->setParameter(1, $options['activity_id'])
                ->setParameter(2, $options['project_id']);

            //    return $qb;
            //     $query = new ActivityFormTypeQuery($options['activities'], $options['projects']);

            //     if (true === $options['query_builder_for_user']) {
            //         $query->setUser($options['user']);
            //     }

            //     if (null !== $options['ignore_activity']) {
            //         $query->setActivityToIgnore($options['ignore_activity']);
            //     }
            //     $activity =  ($options['activities'] == null ? [] : $options['activities']);

            //     return $repo->findOneBy($activity);
            };
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr'] = array_merge($view->vars['attr'], [
            'data-option-pattern' => $this->activityHelper->getChoicePattern(),
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
