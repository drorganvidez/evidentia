// Light entry for the public pages (access, password reset, public signature, block, errors):
// only the theme, the notices and the pieces those pages use, including the ones a component attaches
// on its own (x-ui.field adds charCount to a textarea with maxlength and selectField to a select). The
// authenticated app loads app.js, which registers every component and store.
import { bootAlpine } from './alpine/boot';
import busyForm from './components/busyForm';
import charCount from './components/charCount';
import clipboard from './components/clipboard';
import countUp from './components/countUp';
import dismissible from './components/dismissible';
import notices from './components/notices';
import pageProgress from './components/pageProgress';
import passwordField from './components/passwordField';
import passwordRules from './components/passwordRules';
import selectField from './components/selectField';
import noticesStore from './stores/notices';
import theme from './stores/theme';

bootAlpine({
    components: {
        busyForm,
        charCount,
        clipboard,
        countUp,
        dismissible,
        notices,
        pageProgress,
        passwordField,
        passwordRules,
        selectField,
    },
    stores: { notices: noticesStore, theme },
});
