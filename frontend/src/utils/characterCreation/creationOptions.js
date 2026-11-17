export const modifier = (value) => Math.floor((value - 10) / 2);
export const signed = (value) =>
    value < 0 ? '- ' + Math.abs(value) : '+ ' + value;

// "+ 2" / "- 1" for an already-computed bonus (e.g. a modifier plus a proficiency bonus);
// `compact` drops the space ("+2").
export const formatSigned = (value, { compact = false } = {}) => {
    const text = signed(value);
    return compact ? text.replace(' ', '') : text;
};

// Same, starting from an ability score instead of an already-computed bonus.
export const formatModifier = (score, options) =>
    formatSigned(modifier(score), options);
