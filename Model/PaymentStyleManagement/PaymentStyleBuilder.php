<?php

namespace Bold\Checkout\Model\PaymentStyleManagement;

/**
 * Build the payload for PaymentStyleManagement::update.
 */
class PaymentStyleBuilder
{
    /**
     * @var string[]
     */
    private $cssRules = [];

    /**
     * @var array
     */
    private $mediaRules = [];

    /**
     * Add css rule.
     *
     * @param string $rule
     * @return void
     */
    public function addCssRule(string $rule): void
    {
        $this->cssRules[] = $rule;
    }

    /**
     * Add media rule.
     *
     * @param string $condition
     * @param array $rules
     * @return void
     */
    public function addMediaRule(string $condition, array $rules): void
    {
        $this->mediaRules[$condition] = $rules;
    }

    /**
     * Build payload.
     *
     * @return array
     */
    public function build(): array
    {
        $data = [];
        foreach ($this->cssRules as $rule) {
            $data['css_rules'][]['cssText'] = $rule;
        }
        /** @var array $mediaRule */
        foreach ($this->mediaRules as $condition => $rules) {
            $cssRules = [];
            foreach ($rules as $rule) {
                $cssRules[]['cssText'] = $rule;
            }
            $data['media_rules'][] = [
                'conditionText' => $condition,
                'cssRules' => $cssRules,
            ];
        }

        return $data;
    }
}
