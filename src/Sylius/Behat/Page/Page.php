<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Behat\Page;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Selector\SelectorsHandler;
use Behat\Mink\Session;

/**
 * @author Kamil Kokot <kamil.kokot@lakion.com>
 */
abstract class Page implements PageInterface
{
    /**
     * @var array
     */
    protected $elements = [];

    /**
     * @var Session
     */
    private $session;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var DocumentElement|null
     */
    private $document;

    /**
     * @param Session $session
     * @param array $parameters
     */
    public function __construct(Session $session, array $parameters = [])
    {
        $this->session = $session;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function open(array $urlParameters = [])
    {
        $this->tryToOpen($urlParameters);
        $this->verify($urlParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function tryToOpen(array $urlParameters = [])
    {
        $this->getDriver()->visit($this->getUrl($urlParameters));
    }

    /**
     * {@inheritdoc}
     */
    public function verify(array $urlParameters = [])
    {
        $this->verifyStatusCode();
        $this->verifyUrl($urlParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function isOpen(array $urlParameters = [])
    {
        try {
            $this->verify($urlParameters);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param array $urlParameters
     *
     * @return string
     */
    abstract protected function getUrl(array $urlParameters = []);

    /**
     * @throws UnexpectedPageException
     */
    protected function verifyStatusCode()
    {
        try {
            $statusCode = $this->getDriver()->getStatusCode();
        } catch (DriverException $exception) {
            return; // Ignore drivers which cannot check the response status code
        }

        if ($statusCode >= 200 && $statusCode <= 299) {
            return;
        }

        $currentUrl = $this->getDriver()->getCurrentUrl();
        $message = sprintf('Could not open the page: "%s". Received an error status code: %s', $currentUrl, $statusCode);

        throw new UnexpectedPageException($message);
    }

    /**
     * Overload to verify if the current url matches the expected one. Throw an exception otherwise.
     *
     * @param array $urlParameters
     *
     * @throws UnexpectedPageException
     */
    protected function verifyUrl(array $urlParameters = [])
    {
        if ($this->getDriver()->getCurrentUrl() !== $this->getUrl($urlParameters)) {
            throw new UnexpectedPageException(sprintf('Expected to be on "%s" but found "%s" instead', $this->getUrl($urlParameters), $this->getDriver()->getCurrentUrl()));
        }
    }

    /**
     * @param string $name
     *
     * @return NodeElement
     */
    protected function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    /**
     * @param string $name
     *
     * @return NodeElement
     *
     * @throws ElementNotFoundException
     */
    protected function getElement($name)
    {
        $element = $this->createElement($name);

        if (!$this->getDocument()->has('xpath', $element->getXpath())) {
            throw new ElementNotFoundException(
                $this->getSession(),
                sprintf('Element named "%s"', $name),
                'xpath',
                $element->getXpath()
            );
        }

        return $element;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function hasElement($name)
    {
        return $this->getDocument()->has('xpath', $this->createElement($name)->getXpath());
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this->session;
    }

    /**
     * @return DriverInterface
     */
    protected function getDriver()
    {
        return $this->session->getDriver();
    }

    /**
     * @return DocumentElement
     */
    protected function getDocument()
    {
        if (null === $this->document) {
            $this->document = new DocumentElement($this->session);
        }

        return $this->document;
    }

    /**
     * @param string $name
     *
     * @return NodeElement
     */
    private function createElement($name)
    {
        if (isset($this->elements[$name])) {
            return new NodeElement(
                $this->getSelectorAsXpath($this->elements[$name], $this->session->getSelectorsHandler()),
                $this->session
            );
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @param string|array $selector
     * @param SelectorsHandler $selectorsHandler
     *
     * @return string
     */
    private function getSelectorAsXpath($selector, SelectorsHandler $selectorsHandler)
    {
        $selectorType = is_array($selector) ? key($selector) : 'css';
        $locator = is_array($selector) ? $selector[$selectorType] : $selector;

        return $selectorsHandler->selectorToXpath($selectorType, $locator);
    }
}
