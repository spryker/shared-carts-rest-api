<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SharedCartsRestApi\Communication\Controller;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShareCartRequestTransfer;
use Generated\Shared\Transfer\ShareCartResponseTransfer;
use Generated\Shared\Transfer\ShareDetailCollectionTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method \Spryker\Zed\SharedCartsRestApi\Business\SharedCartsRestApiFacadeInterface getFacade()
 * @method \Spryker\Zed\SharedCartsRestApi\Communication\SharedCartsRestApiCommunicationFactory getFactory()
 */
class GatewayController extends AbstractGatewayController
{
    public function getSharedCartsByCartUuidAction(QuoteTransfer $quoteTransfer): ShareDetailCollectionTransfer
    {
        return $this->getFacade()->getSharedCartsByCartUuid($quoteTransfer);
    }

    public function createAction(ShareCartRequestTransfer $shareCartRequestTransfer): ShareCartResponseTransfer
    {
        return $this->getFacade()->create($shareCartRequestTransfer);
    }

    public function updateAction(ShareCartRequestTransfer $shareCartRequestTransfer): ShareCartResponseTransfer
    {
        return $this->getFacade()->update($shareCartRequestTransfer);
    }

    public function deleteAction(ShareCartRequestTransfer $shareCartRequestTransfer): ShareCartResponseTransfer
    {
        return $this->getFacade()->delete($shareCartRequestTransfer);
    }
}
