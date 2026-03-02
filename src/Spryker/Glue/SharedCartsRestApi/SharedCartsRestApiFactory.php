<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\SharedCartsRestApi;

use Spryker\Glue\Kernel\AbstractFactory;
use Spryker\Glue\SharedCartsRestApi\Processor\RestResponseBuilder\SharedCartRestResponseBuilder;
use Spryker\Glue\SharedCartsRestApi\Processor\RestResponseBuilder\SharedCartRestResponseBuilderInterface;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\Mapper\SharedCartMapper;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\Mapper\SharedCartMapperInterface;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\Relationship\SharedCartByCartIdExpander;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\Relationship\SharedCartByCartIdExpanderInterface;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\SharedCartCreator;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\SharedCartCreatorInterface;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\SharedCartDeleter;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\SharedCartDeleterInterface;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\SharedCartUpdater;
use Spryker\Glue\SharedCartsRestApi\Processor\SharedCart\SharedCartUpdaterInterface;
use Spryker\Glue\SharedCartsRestApiExtension\Dependency\Plugin\CompanyUserProviderPluginInterface;

/**
 * @method \Spryker\Client\SharedCartsRestApi\SharedCartsRestApiClientInterface getClient()
 * @method \Spryker\Glue\SharedCartsRestApi\SharedCartsRestApiConfig getConfig()
 */
class SharedCartsRestApiFactory extends AbstractFactory
{
    public function createSharedCartByCartIdExpander(): SharedCartByCartIdExpanderInterface
    {
        return new SharedCartByCartIdExpander(
            $this->getClient(),
            $this->createSharedCartMapper(),
            $this->getResourceBuilder(),
        );
    }

    public function createSharedCartCreator(): SharedCartCreatorInterface
    {
        return new SharedCartCreator(
            $this->getClient(),
            $this->createSharedCartRestResponseBuilder(),
            $this->getCompanyUserProviderPlugin(),
        );
    }

    public function createSharedCartUpdater(): SharedCartUpdaterInterface
    {
        return new SharedCartUpdater(
            $this->getClient(),
            $this->createSharedCartRestResponseBuilder(),
        );
    }

    public function createSharedCartDeleter(): SharedCartDeleterInterface
    {
        return new SharedCartDeleter(
            $this->getClient(),
            $this->createSharedCartRestResponseBuilder(),
        );
    }

    public function createSharedCartRestResponseBuilder(): SharedCartRestResponseBuilderInterface
    {
        return new SharedCartRestResponseBuilder(
            $this->getResourceBuilder(),
            $this->createSharedCartMapper(),
            $this->getConfig(),
        );
    }

    public function createSharedCartMapper(): SharedCartMapperInterface
    {
        return new SharedCartMapper();
    }

    public function getCompanyUserProviderPlugin(): CompanyUserProviderPluginInterface
    {
        return $this->getProvidedDependency(SharedCartsRestApiDependencyProvider::PLUGIN_COMPANY_USER_PROVIDER);
    }
}
