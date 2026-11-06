/**
 * Text ready for a search that ignores case and accents: «Logística» and «LOGISTICA» both give «logistica».
 * Shared by the modules that filter a list as the person types; not an Alpine component.
 */
export function normalize(text) {
    return text
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase()
        .trim();
}
