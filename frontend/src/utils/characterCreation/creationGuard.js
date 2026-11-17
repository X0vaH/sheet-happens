import { scoreBounds } from '../../configs/characterCreation.js';

export const validScore = (value) =>
    Number.isInteger(value) &&
    value >= scoreBounds.min &&
    value <= scoreBounds.max;
