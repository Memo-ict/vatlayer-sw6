import './components/memo-vatlayer-api-credentials';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';
import nlNL from './snippet/nl-NL.json'

const { Module } = Shopware;

Module.register('memo-vatlayer', {
    type: 'plugin',
    name: 'MemoVatlayer',
    title: 'memo-vatlayer.general.title',
    description: 'memo-vatlayer.general.description',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#333',
    icon: 'default-action-settings',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB,
        'nl-NL': nlNL
    }
});
