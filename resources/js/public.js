// Light entry for the public pages (access, password reset, public signature, block, errors):
// only the theme, the notices and the pieces those pages use. The authenticated app loads app.js,
// which registers every component and store.
import { bootAlpine } from './alpine/boot';
import busyForm from './components/busyForm';
import clipboard from './components/clipboard';
import countUp from './components/countUp';
import dismissible from './components/dismissible';
import notices from './components/notices';
import passwordField from './components/passwordField';
import noticesStore from './stores/notices';
import theme from './stores/theme';

bootAlpine({
    components: { busyForm, clipboard, countUp, dismissible, notices, passwordField },
    stores: { notices: noticesStore, theme },
});
