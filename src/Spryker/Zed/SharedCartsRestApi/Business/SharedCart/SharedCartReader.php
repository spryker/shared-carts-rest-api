<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SharedCartsRestApi\Business\SharedCart;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShareDetailCollectionTransfer;
use Spryker\Zed\SharedCartsRestApi\Dependency\Facade\SharedCartsRestApiToQuoteFacadeInterface;
use Spryker\Zed\SharedCartsRestApi\Dependency\Facade\SharedCartsRestApiToSharedCartFacadeInterface;

class SharedCartReader implements SharedCartReaderInterface
{
    /**
     * @var \Spryker\Zed\SharedCartsRestApi\Dependency\Facade\SharedCartsRestApiToQuoteFacadeInterface
     */
    protected $quoteFacade;

    /**
     * @var \Spryker\Zed\SharedCartsRestApi\Dependency\Facade\SharedCartsRestApiToSharedCartFacadeInterface
     */
    protected $sharedCartFacade;

    public function __construct(
        SharedCartsRestApiToQuoteFacadeInterface $quoteFacade,
        SharedCartsRestApiToSharedCartFacadeInterface $sharedCartFacade
    ) {
        $this->quoteFacade = $quoteFacade;
        $this->sharedCartFacade = $sharedCartFacade;
    }

    public function getSharedCartsByCartUuid(QuoteTransfer $quoteTransfer): ShareDetailCollectionTransfer
    {
        $quoteResponseTransfer = $this->quoteFacade->findQuoteByUuid($quoteTransfer);
        if (!$quoteResponseTransfer->getQuoteTransfer()) {
            return new ShareDetailCollectionTransfer();
        }

        return $this->sharedCartFacade->getShareDetailsByIdQuote($quoteResponseTransfer->getQuoteTransfer());
    }
}
