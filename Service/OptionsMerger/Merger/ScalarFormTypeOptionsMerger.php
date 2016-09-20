<?php


namespace Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger;


use Barbieswimcrew\Bundle\SymfonyFormRuleSetBundle\Service\OptionsMerger\Merger\Base\AbstractOptionsMerger;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

class ScalarFormTypeOptionsMerger extends AbstractOptionsMerger
{
    /**
     * @deprecated
     */
    const CSS_HIDDEN_CLASS = "hidden";

    public function getMergedOptions(FormInterface $form, array $overrideOptions, $hidden)
    {
        /** @var array $originOptions */
        $originOptions = $form->getConfig()->getOptions();

        # array recursive because the options array contains other arrays to be merged (attr,...)
        $merged = array_merge($originOptions, $overrideOptions, array('auto_initialize' => false));

        # string concatenation for css classes
        if (isset($originOptions['attr']['class']) and isset($overrideOptions['attr']['class'])) {
            $merged['attr']['class'] = $this->mergeAttrClasses($originOptions['attr']['class'], $overrideOptions['attr']['class']);
        }

        # string concatenation for label css classes
        if (isset($originOptions['label_attr']['class']) and isset($overrideOptions['label_attr']['class'])) {
            $merged['label_attr']['class'] = $this->mergeAttrClasses($originOptions['label_attr']['class'], $overrideOptions['label_attr']['class']);
        }

        $merged['attr']['class'] = $this->handleHiddenClass($merged['attr'], $hidden);
        $merged['label_attr']['class'] = $this->handleHiddenClass($merged['label_attr'], $hidden);

        return $merged;
    }

    /**
     * @author Anton Zoffmann
     * @return string
     */
    protected function getApplicableInterface()
    {
        return FormTypeInterface::class;
    }

    /**
     * @author Anton Zoffmann
     * @return array
     */
    protected function getApplicableClasses()
    {
        return array();
    }

    /**
     * merge class strings
     * @param string $originClasses
     * @param string $overrideClasses
     * @return string
     * @author Anton Zoffmann
     */
    protected function mergeAttrClasses($originClasses, $overrideClasses)
    {
        $originClasses = explode(' ', $originClasses);
        $overrideClasses = explode(' ', $overrideClasses);

        return implode(' ', array_merge($originClasses, $overrideClasses));
    }

    /**
     * @param array $attributes
     * @param boolean $hidden
     * @author Anton Zoffmann
     * @return string
     */
    protected function handleHiddenClass(array $attributes, $hidden)
    {
        # define classes string for further handling
        if (isset($attributes['class'])) {
            $classes = $attributes['class'];
        } elseif ($hidden === true) {
            return self::CSS_HIDDEN_CLASS;
        } else {
            $classes = "";
        }

        $hiddenContained = strpos($classes, self::CSS_HIDDEN_CLASS) !== false;

        if ($hiddenContained and $hidden === false) {
            # if hidden must be removed
            $classes = explode(' ', $classes);
            $key = array_search(self::CSS_HIDDEN_CLASS, $classes);
            unset($classes[$key]);

            return implode(' ', $classes);
        }

        if (!$hiddenContained and $hidden === true) {
            # if hidden must be added
            return sprintf("%s %s", $classes, self::CSS_HIDDEN_CLASS);
        }

        return $classes;
    }

}