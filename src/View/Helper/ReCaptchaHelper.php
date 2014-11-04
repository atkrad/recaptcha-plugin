<?php

namespace Recaptcha\View\Helper;

use Cake\View\Exception\MissingElementException;
use Cake\View\Helper;
use Cake\View\Helper\FormHelper;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use ReCaptcha\Lib\ReCaptcha;

/**
 * ReCaptcha Helper
 *
 * @property HtmlHelper      Html
 * @property FormHelper      Form
 *
 * @package Recaptcha\View\Helper
 */
class ReCaptchaHelper extends Helper
{
    /**
     * List of helpers used by ReCaptcha helper
     *
     * @var array
     */
    public $helpers = ['Html', 'Form'];

    /**
     * Default config for ReCaptch helper.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'theme' => ['name' => self::THEME_DEFAULT, 'element' => self::ELEMENT_DEFAULT]
    ];

    const THEME_CUSTOM = 'custom';
    const THEME_DEFAULT = 'default';
    const THEME_BOOTSTRAP = 'bootstrap';

    const ELEMENT_DEFAULT = 'ReCaptcha.default';
    const ELEMENT_BOOTSTRAP = 'ReCaptcha.bootstrap';

    /**
     * Display reCaptcha
     *
     * @return string
     * @throws \Exception
     */
    public function display()
    {
        return $this->_View->element(
            $this->getElementName(),
            [
                'challengeAddress' => ReCaptcha::getChallengeUri(),
                'noScriptAddress' => ReCaptcha::getNoScriptUri()
            ]
        );
    }

    /**
     * Get element name
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getElementName()
    {
        switch ($this->config('theme.name')) {
            case self::THEME_DEFAULT:
                return self::ELEMENT_DEFAULT;

            case self::THEME_BOOTSTRAP:
                return self::ELEMENT_BOOTSTRAP;

            case self::THEME_CUSTOM:
                if (!$this->_View->elementExists($this->config('theme.element'))) {
                    throw new MissingElementException('Element file "%s" is missing.');
                }

                return $this->config('theme.element');

            default:
                throw new \Exception('Theme name is not valid.');
        }
    }
}
