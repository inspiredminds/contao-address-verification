<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoAddressVerification\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use Haste\Form\Form;
use InspiredMinds\ContaoAddressVerification\Event\BuildAddressVerificationFormEvent;
use InspiredMinds\ContaoAddressVerification\Model\AddressModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @FrontendModule(AddressVerificationController::TYPE, category="address_verification")
 */
class AddressVerificationController extends AbstractFrontendModuleController
{
    public const TYPE = 'address_verification';

    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function getResponse(Template $template, ModuleModel $module, Request $request): Response
    {
        $form = $this->buildForm($module, $request);

        $template->form = $form->generate();
        $template->formId = $this->getFormId($module);
        $template->addresses = $this->getAddresses($module);

        //$GLOBALS['TL_CSS'][] = 'bundles/contaoaddressverification/css/autoComplete.css';

        return new Response($template->parse());
    }

    private function buildForm(ModuleModel $module, Request $request): Form
    {
        $form = new Form($this->getFormId($module), 'POST', function (Form $form) use ($request) {
            return $request->request->get('FORM_SUBMIT') === $form->getFormId();
        });

        $form->addFieldsFromDca('tl_address', function (string $field, array $def) use ($module) {
            if (empty($def['inputType'])) {
                return false;
            }

            if (!(bool) $module->address_country && 'country' === $field) {
                return false;
            }

            return true;
        });

        $form->addSubmitFormField('submit', 'Submit');

        $this->eventDispatcher->dispatch(new BuildAddressVerificationFormEvent($form));

        return $form;
    }

    private function getFormId(ModuleModel $model): string
    {
        return 'address-verification-'.$model->id;
    }

    /**
     * @return array<AddressModel>
     */
    private function getAddresses(ModuleModel $model): array
    {
        $addresses = AddressModel::findByPids(StringUtil::deserialize($model->address_groups, true));

        if (null === $addresses) {
            return [];
        }

        Controller::loadDataContainer('tl_address');
        $dcaFields = $GLOBALS['TL_DCA']['tl_address']['fields'];

        return array_map(
            function (AddressModel $address) use ($dcaFields, $model) {
                $row = $address->row();

                foreach ($row as $key => $value) {
                    if (empty($dcaFields[$key]['inputType'])) {
                        unset($row[$key]);
                    }
                }

                // Build a human readable address for the autocomplete selection
                $row['address'] = $address->getReadableAddress((bool) $model->address_country);

                return $row;
            }, $addresses->getModels()
        );
    }
}
