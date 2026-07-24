import registerHasMultipleConditions from './cp/utilities/has-multiple.js';
import registerNotContainsCondition from './cp/utilities/not-contains.js';
import registerRequiresLabelConditions from './cp/utilities/requires-label.js';

Statamic.booting(() => {
    registerRequiresLabelConditions();
    registerNotContainsCondition();
    registerHasMultipleConditions();
});