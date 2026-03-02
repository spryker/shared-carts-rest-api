<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SharedCartsRestApi\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\SharedCartsRestApi\Business\QuotePermissionGroup\QuotePermissionGroupReader;
use Spryker\Zed\SharedCartsRestApi\Business\QuotePermissionGroup\QuotePermissionGroupReaderInterface;
use Spryker\Zed\SharedCartsRestApi\Business\SharedCart\SharedCartCreator;
use Spryker\Zed\SharedCartsRestApi\Business\SharedCart\SharedCartCreatorInterface;
use Spryker\Zed\SharedCartsRestApi\Business\SharedCart\SharedCartDeleter;
use Spryker\Zed\SharedCartsRestApi\Business\SharedCart\SharedCartDeleterInterface;
use Spryker\Zed\SharedCartsRestApi\Business\SharedCart\SharedCartReader;
use Spryker\Zed\SharedCartsRestApi\Business\SharedCart\SharedCartReaderInterface;
use Spryker\Zed\SharedCartsRestApi\Business\SharedCart\SharedCartUpdater;
use Spryker\Zed\SharedCartsRestApi\Business\SharedCart\SharedCartUpdaterInterface;
use Spryker\Zed\SharedCartsRestApi\Dependency\Facade\SharedCartsRestApiToQuoteFacadeInterface;
use Spryker\Zed\SharedCartsRestApi\Dependency\Facade\SharedCartsRestApiToSharedCartFacadeInterface;
use Spryker\Zed\SharedCartsRestApi\SharedCartsRestApiDependencyProvider;

/**
 * @method \Spryker\Zed\SharedCartsRestApi\SharedCartsRestApiConfig getConfig()
 */
class SharedCartsRestApiBusinessFactory extends AbstractBusinessFactory
{
    public function createSharedCartReader(): SharedCartReaderInterface
    {
        return new SharedCartReader(
            $this->getQuoteFacade(),
            $this->getSharedCartFacade(),
        );
    }

    public function createSharedCartCreator(): SharedCartCreatorInterface
    {
        return new SharedCartCreator(
            $this->getQuoteFacade(),
            $this->getSharedCartFacade(),
        );
    }

    public function createSharedCartUpdater(): SharedCartUpdaterInterface
    {
        return new SharedCartUpdater($this->getSharedCartFacade());
    }

    public function createSharedCartDeleter(): SharedCartDeleterInterface
    {
        return new SharedCartDeleter($this->getSharedCartFacade());
    }

    public function createQuotePermissionGroupReader(): QuotePermissionGroupReaderInterface
    {
        return new QuotePermissionGroupReader($this->getSharedCartFacade());
    }

    public function getQuoteFacade(): SharedCartsRestApiToQuoteFacadeInterface
    {
        return $this->getProvidedDependency(SharedCartsRestApiDependencyProvider::FACADE_QUOTE);
    }

    public function getSharedCartFacade(): SharedCartsRestApiToSharedCartFacadeInterface
    {
        return $this->getProvidedDependency(SharedCartsRestApiDependencyProvider::FACADE_SHARED_CART);
    }
}
