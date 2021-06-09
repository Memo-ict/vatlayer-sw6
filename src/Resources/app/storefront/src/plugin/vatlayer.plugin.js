import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import Debouncer from 'src/helper/debouncer.helper';
import HttpClient from 'src/service/http-client.service';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

export default class VatlayerPlugin extends Plugin {
    static options = {
        url: window.router['frontend.memo.vatlayer.validate'],
        csrfToken: ''
    }

    init() {
        this._client = new HttpClient();
        this._registerEvents();
    }


    _checkVatId() {
        const data = this._getRequestData();

        ElementLoadingIndicatorUtil.create(this.el);

        this._client.post(this.options.url, JSON.stringify(data), content => this._parseRequest(JSON.parse(content)));
    }

    _parseRequest(data) {
        console.log(data)

        ElementLoadingIndicatorUtil.remove(this.el);
    }

    _getRequestData() {
        const data = {
        };

        if (window.csrf.enabled && window.csrf.mode === 'twig') {
            data['_csrf_token'] = this.options.csrfToken;
        }

        return data;
    }

    _registerEvents() {
        const me = this;

        const checkVatId = me._checkVatId.bind(me);
        me.el.addEventListener('blur', checkVatId);
        me.el.addEventListener('keydown', Debouncer.debounce(checkVatId, 500));
    }
}
