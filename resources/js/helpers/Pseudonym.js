export function getFullPseudonym(firstName, lastName) {
    return checkString(firstName) + ' ' + checkString(lastName);
}

export function getShortPseudonym(firstName, lastName) {
    return checkString(firstName).charAt(0) + '. ' + checkString(lastName);
}

function checkString(name) {
    if (name === undefined || name === null) return 'Unbekannt';
    return name;
}
