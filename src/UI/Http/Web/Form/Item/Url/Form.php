<?php

/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Zentlix to newer
 * versions in the future. If you wish to customize Zentlix for your
 * needs please refer to https://docs.zentlix.io for more information.
 */

declare(strict_types=1);

namespace Zentlix\MenuBundle\UI\Http\Web\Form\Item\Url;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Zentlix\MainBundle\UI\Http\Web\Type;
use Zentlix\MenuBundle\Application\Command\Item\Url\Command;
use Zentlix\MenuBundle\Application\Command\Item\Url\UpdateCommand;
use Zentlix\MenuBundle\Domain\Menu\Repository\ItemRepository;
use function is_bool;

class Form extends AbstractType
{
    protected EventDispatcherInterface $eventDispatcher;
    protected TranslatorInterface $translator;
    protected ItemRepository $itemRepository;

    public function __construct(EventDispatcherInterface $eventDispatcher,
                                TranslatorInterface $translator,
                                ItemRepository $itemRepository)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->itemRepository = $itemRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Command $command */
        $command = $builder->getData();

        $menuItems = $this->itemRepository->assocByRootId($command->getMenu()->getRootItem()->getId());
        if($command instanceof UpdateCommand) {
            if($key = array_search($command->getEntity()->getId(), $menuItems)) {
                if(is_bool($key) === false) {
                    unset($menuItems[$key]);
                }
            }
        }

        $builder
            ->add('title', Type\TextType::class, [
                'label' => 'zentlix_menu.item.item'
            ])
            ->add('url', Type\TextType::class, [
                'label' => 'zentlix_main.site.url'
            ])
            ->add('blank', Type\CheckboxType::class, [
                'label' => 'zentlix_menu.target_blank'
            ])
            ->add('sort', Type\IntegerType::class, [
                'label'       => 'zentlix_main.sort',
                'constraints' => [
                    new GreaterThan(['value' => 0, 'message' => $this->translator->trans('zentlix_main.validation.greater_0')])
                ]
            ])
            ->add('parent', Type\ChoiceType::class, [
                'choices'  => $menuItems,
                'label'    => 'zentlix_menu.item.parent'
            ]);
    }
}