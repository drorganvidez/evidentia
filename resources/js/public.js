// Light entry for the public pages (access, password reset, public signature, block, errors):
// only the theme and the pieces those pages use. The authenticated app loads app.js instead.
import { bootAlpine } from './alpine/boot';
import clipboard from './components/clipboard';
import theme from './stores/theme';

bootAlpine({ components: { clipboard }, stores: { theme } });
