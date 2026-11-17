import { useContext } from 'react';
import { CharacterLimitContext } from '../context/CharacterLimitContext.js';

// Whether the user already owns the maximum number of characters (see CharacterLimitProvider).
export function useCharacterLimit() {
    const context = useContext(CharacterLimitContext);
    if (!context) {
        throw new Error(
            "useCharacterLimit doit être utilisé à l'intérieur d'un CharacterLimitProvider",
        );
    }
    return context;
}
