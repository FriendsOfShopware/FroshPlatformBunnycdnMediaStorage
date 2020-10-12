const ApiService = Shopware.Classes.ApiService;
const { Application } = Shopware;

class ApiClient extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'bunnycdn-api-test') {
        super(httpClient, loginService, apiEndpoint);
    }

    check(values) {
        const headers = this.getBasicHeaders({});

        return this.httpClient
            .post(`_action/${this.getApiBasePath()}/check`, values,{
                headers
            })
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

Application.addServiceProvider('bunnycdnApiTest', (container) => {
    const initContainer = Application.getContainer('init');
    return new ApiClient(initContainer.httpClient, container.loginService);
});
