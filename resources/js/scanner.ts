const SCANNER_TIMEOUT = 1000;

export default function scanner(callback: (buffer: string) => void) {
    let buffer = "";
    let timeout: number | null = null;

    const clearBuffer = () => {
        buffer = "";
        clearTimeout(timeout!);
        timeout = null;
    };

    const resetTimer = () => {
        clearTimeout(timeout!);
        timeout = setTimeout(clearBuffer, SCANNER_TIMEOUT);
    };

    document.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            if (buffer.length > 0) callback(buffer);
            clearBuffer();
        } else if (e.key === "Backspace") {
            buffer = buffer.slice(0, -1);
            resetTimer();
        } else if (e.key.length === 1) {
            buffer += e.key;
            resetTimer();
        }
    });
}
