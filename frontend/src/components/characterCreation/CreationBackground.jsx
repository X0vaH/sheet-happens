import { useEffect, useRef, useState } from 'react';
import background from '../../assets/creation-background.png';

export default function CreationBackground({ src = background }) {
    const containerRef = useRef(null);
    const [tileCount, setTileCount] = useState(2);

    useEffect(() => {
        const container = containerRef.current;
        const firstTile = container.firstElementChild;
        const observer = new ResizeObserver(() => {
            const tileHeight = firstTile.getBoundingClientRect().height;
            if (tileHeight > 0) {
                setTileCount(
                    Math.max(2, Math.ceil(container.clientHeight / tileHeight)),
                );
            }
        });
        observer.observe(container);
        observer.observe(firstTile);
        return () => observer.disconnect();
    }, []);

    return (
        <div
            className="creation-background-tiles"
            ref={containerRef}
            aria-hidden="true"
        >
            {Array.from({ length: tileCount }, (_, index) => (
                <img
                    key={index}
                    className={
                        index === 0
                            ? 'creation-background creation-background-tile'
                            : 'creation-background-tile'
                    }
                    src={src}
                    alt=""
                    draggable={false}
                />
            ))}
        </div>
    );
}
