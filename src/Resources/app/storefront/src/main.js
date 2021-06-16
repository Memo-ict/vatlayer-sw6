import PluginManager from 'src/plugin-system/plugin.manager';

import VatlayerPlugin from './plugin/vatlayer.plugin';

PluginManager.register('Vatlayer', VatlayerPlugin, '[data-vatlayer]');
