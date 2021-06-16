import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import Debouncer from 'src/helper/debouncer.helper';
import HttpClient from 'src/service/http-client.service';
// import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

export default class VatlayerPlugin extends Plugin {
    static options = {
        url: window.router['frontend.memo.vatlayer.validate'],
        csrfToken: ''
    }

    init() {
        this._client = new HttpClient();
        this._createContainers();
        this._registerEvents();
    }

    _createContainers() {
        this._container = document.createElement('div');
        this._container.classList.add('vatlayer-container');
        this.el.insertAdjacentElement('beforebegin', this._container);
        this._container.insertAdjacentElement('afterbegin', this.el);

        this._message = document.createElement('div');
        this._message.classList.add('vatlayer-message');
        this._container.insertAdjacentElement('beforeend', this._message);
        this._message_content = document.createElement('div');
        this._message_content.classList.add('vatlayer-message-content');
        this._message.insertAdjacentElement('beforeend', this._message_content);
    }

    _checkVatId() {
        const data = this._getRequestData();

        if(data) {
            this._client.post(this.options.url, JSON.stringify(data), content => this._parseRequest(JSON.parse(content)));
        }
    }

    _parseRequest(data) {
        this.el.classList.forEach(className => {
            if(className.indexOf('alert') >= 0) {
                this.el.classList.remove(className);
            }
        });
        this.el.classList.add(`alert-${data.message.type}`);

        this._message.classList.forEach(className => {
            if(className.indexOf('alert') >= 0) {
                this._message.classList.remove(className);
            }
        });
        this._message.classList.add(`alert-${data.message.type}`)

        this.el.title = data.message.message;
        this._message_content.innerHTML = data.message.message;
    }

    _getRequestData() {
        if(this.el.value.trimEnd().length === 0) {
            return;
        }

        const data = {
            vatId: this.el.value
        };

        if (window.csrf.enabled && window.csrf.mode === 'twig') {
            data['_csrf_token'] = this.options.csrfToken;
        }

        return data;
    }

    _registerEvents() {
        const me = this;

        const checkVatId = me._checkVatId.bind(me);
        const debounced = Debouncer.debounce(checkVatId, 1000);
        this.el.addEventListener('blur', debounced);
        this.el.addEventListener('keydown', debounced);
    }
}
