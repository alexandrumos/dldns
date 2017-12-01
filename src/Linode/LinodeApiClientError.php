<?php

namespace Dldns\Linode;

class LinodeApiClientError {

    const HTTP_ERROR_INVALID_REQUEST  = 400;
    const HTTP_ERROR_AUTH_FAIL        = 401;
    const HTTP_ERROR_PERMISSION_FAIL  = 403;
    const HTTP_ERROR_MISSING_RESOURCE = 404;
    const HTTP_ERROR_LIMIT            = 429;
    const HTTP_ERROR_SUPPORT          = 500;

}