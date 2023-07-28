<?php
declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * "Lightweight Frontend Experience (LiFE) Elements" dynamic row.
 */
class LifeElements extends AbstractFieldArray
{
    /**
     * @var Location
     */
    private $locationRenderer;

    /**
     * @var InputType
     */
    private $inputTypeRenderer;

    /**
     * @var InputRequired
     */
    private $inputRequiredRenderer;

    /**
     * @inheirtDoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'location',
            [
                'label' => __('Location'),
                'renderer' => $this->getLocationRenderer(),
            ]
        );
        $this->addColumn(
            'input_type',
            [
                'label' => __('Type'),
                'renderer' => $this->getInputTypeRenderer(),
            ]
        );
        $this->addColumn(
            'input_required',
            [
                'label' => __('Required'),
                'renderer' => $this->getInputRequiredRenderer(),
            ]
        );
        $this->addColumn('input_label', ['label' => __('Label')]);
        $this->addColumn('input_placeholder', ['label' => __('Placeholder')]);
        $this->addColumn(
            'meta_data_field',
            [
                'label' => __('Field key'),
                'class' => 'required-entry'
            ]
        );
        $this->addColumn('input_default', ['label' => __('Default')]);
        $this->addColumn(
            'order_asc',
            [
                'label' => __('Index'),
                'class' => 'required-entry'
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @inheirtDoc
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $location = $row->getData('location');
        $inputType = $row->getData('input_type');
        $inputRequired = $row->getData('input_required');

        if ($location) {
            $options['option_' . $this->getLocationRenderer()->calcOptionHash($location)] = 'selected="selected"';
        }

        if ($inputType) {
            $options['option_' . $this->getInputTypeRenderer()->calcOptionHash($inputType)] = 'selected="selected"';
        }

        if ($inputRequired) {
            $options['option_' . $this->getInputRequiredRenderer()->calcOptionHash($inputRequired)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Retrieve renderer for location element.
     *
     * @return Location
     */
    private function getLocationRenderer(): Location
    {
        if (!$this->locationRenderer) {
            $this->locationRenderer = $this->getLayout()->createBlock(
                Location::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->locationRenderer;
    }

    /**
     * Retrieve renderer for input type element.
     *
     * @return InputType
     */
    private function getInputTypeRenderer(): InputType
    {
        if (!$this->inputTypeRenderer) {
            $this->inputTypeRenderer = $this->getLayout()->createBlock(
                InputType::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->inputTypeRenderer;
    }

    /**
     * Retrieve renderer for input required element.
     *
     * @return InputRequired
     */
    private function getInputRequiredRenderer(): InputRequired
    {
        if (!$this->inputRequiredRenderer) {
            $this->inputRequiredRenderer = $this->getLayout()->createBlock(
                InputRequired::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->inputRequiredRenderer;
    }
}
