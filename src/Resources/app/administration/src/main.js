import './service/bunnycdnApiTestService';
import './component/bunnycdn-api-test-button';
import './component/bunnycdn-config-restriction';
import './component/bunnycdn-alert';

import localeDE from './snippet/de_DE.json';
import localeEN from './snippet/en_GB.json';

Shopware.Locale.extend('de-DE', localeDE);
Shopware.Locale.extend('en-GB', localeEN);
