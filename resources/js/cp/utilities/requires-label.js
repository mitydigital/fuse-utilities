const targetRequiresLabel = (target) => {
    if (! target) {
        return false;
    }

    return ! (
        target.startsWith('asset::') ||
        target.startsWith('entry::') ||
        target.startsWith('@')
    );
};

export default function registerRequiresLabelConditions() {
    Statamic.$conditions.add('requiresLabel', ({ target }) => {
        return targetRequiresLabel(target);
    });

    Statamic.$conditions.add('doesNotRequireLabel', ({ target }) => {
        return ! targetRequiresLabel(target);
    });
}