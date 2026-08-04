export default function registerNotContainsCondition() {
    Statamic.$conditions.add('not_contains', ({ target, params }) => {
        if (!target) {
            return true;
        }

        for (let i = 0; i < params.length; i++) {
            if (target.includes(params[i])) {
                return false;
            }
        }

        return true;
    });
}