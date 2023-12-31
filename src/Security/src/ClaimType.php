<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security;

/**
 * Defines the different types of claims
 */
enum ClaimType: string
{
    case Actor = 'http://schemas.xmlsoap.org/ws/2009/09/identity/claims/actor';
    case Anonymous = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/anonymous';
    case Authentication = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/authenticated';
    case AuthorizationDecision = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/authorizationdecision';
    case Country = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/country';
    case DateOfBirth = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/dateofbirth';
    case DenyOnlySid = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/denyonlysid';
    case Dns = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/dns';
    case Email = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/email';
    case Gender = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/gender';
    case GivenName = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname';
    case Hash = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/hash';
    case HomePhone = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/homephone';
    case Locality = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/locality';
    case MobilePhone = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/mobilephone';
    case Name = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name';
    case NameIdentifier = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/nameidentifier';
    case OtherPhone = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/otherphone';
    case PostalCode = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/postalcode';
    case Role = 'http://schemas.microsoft.com/ws/2008/06/identity/claims/role';
    case Rsa = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/rsa';
    case Sid = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/sid';
    case Spn = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/spn';
    case StateOrProvince = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/stateorprovince';
    case StreetAddress = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/streetaddress';
    case Surname = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname';
    case System = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/system';
    case Thumbprint = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/thumbprint';
    case Upn = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/upn';
    case Uri = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/uri';
    case WebPage = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/webpage';
    case X500DistinguishedName = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/x500distinguishedname';
}
