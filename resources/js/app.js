import { bootAlpine } from './alpine/boot';
import * as components from './components';
import theme from './stores/theme';

bootAlpine({ components, stores: { theme } });
