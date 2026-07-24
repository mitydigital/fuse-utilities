export default function registerHasMultipleConditions() {
    Statamic.$conditions.add('hasMultiple', ({ target, values }) => {
        return target.length > 1;
    });
    Statamic.$conditions.add('doesNotHaveMultiple', ({ target, values }) => {
        return target.length <= 1;
    });
}