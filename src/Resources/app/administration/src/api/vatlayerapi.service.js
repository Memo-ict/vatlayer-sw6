const { Application } = Shopware;
const ApiService = Shopware.Classes.ApiService;

class VatlayerApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint) {
        super(httpClient, loginService, apiEndpoint);
    }

    checkCredentials() {
        return this.httpClient
            .get(`${this.getApiBasePath()}/check-credentials`, {headers: this.getBasicHeaders()})
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

Application.addServiceProvider('VatlayerApiService', (container) => {
    const initContainer = Application.getContainer('init');

    return new VatlayerApiService(initContainer.httpClient, container.loginService, 'memo/vatlayer');
});
