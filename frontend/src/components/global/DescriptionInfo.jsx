import { useEffect, useId, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import '../../styles/DescriptionInfo.css';

const VIEWPORT_MARGIN = 12;
const TOOLTIP_GAP = 8;
const TOOLTIP_WIDTH = 320;

/** Shows contextual information on mouse hover, keyboard focus or an explicit tap. */
export default function DescriptionInfo({ label, description, children }) {
    const id = useId();
    const trigger = useRef(null);
    const [position, setPosition] = useState(null);
    const open = Boolean(position);

    function show() {
        const rect = trigger.current.getBoundingClientRect();
        const width = Math.min(
            TOOLTIP_WIDTH,
            window.innerWidth - VIEWPORT_MARGIN * 2,
        );
        const below = rect.bottom < window.innerHeight / 2;
        setPosition({
            left: Math.max(
                VIEWPORT_MARGIN,
                Math.min(
                    rect.left,
                    window.innerWidth - width - VIEWPORT_MARGIN,
                ),
            ),
            width,
            ...(below
                ? {
                      top: rect.bottom + TOOLTIP_GAP,
                      maxHeight:
                          window.innerHeight -
                          rect.bottom -
                          TOOLTIP_GAP -
                          VIEWPORT_MARGIN,
                  }
                : {
                      bottom: window.innerHeight - rect.top + TOOLTIP_GAP,
                      maxHeight: rect.top - TOOLTIP_GAP - VIEWPORT_MARGIN,
                  }),
        });
    }

    useEffect(() => {
        if (!open) return;
        const dismiss = () => setPosition(null);
        const outside = (event) => {
            if (!trigger.current.contains(event.target)) dismiss();
        };
        const keydown = (event) => {
            if (event.key === 'Escape') dismiss();
        };
        document.addEventListener('pointerdown', outside, true);
        document.addEventListener('keydown', keydown);
        window.addEventListener('resize', dismiss);
        document.addEventListener('scroll', dismiss, true);
        return () => {
            document.removeEventListener('pointerdown', outside, true);
            document.removeEventListener('keydown', keydown);
            window.removeEventListener('resize', dismiss);
            document.removeEventListener('scroll', dismiss, true);
        };
    }, [open]);

    return (
        <div
            className="description-info"
            ref={trigger}
            onPointerEnter={(event) => {
                if (
                    event.pointerType === 'mouse' &&
                    window.matchMedia('(hover: hover) and (pointer: fine)')
                        .matches
                )
                    show();
            }}
            onPointerLeave={(event) => {
                if (event.pointerType === 'mouse') setPosition(null);
            }}
            onFocus={(event) => {
                if (event.target.matches(':focus-visible')) show();
            }}
            onBlur={(event) => {
                if (!event.currentTarget.contains(event.relatedTarget))
                    setPosition(null);
            }}
        >
            {children}
            <button
                className="description-info-button"
                type="button"
                aria-label={label}
                aria-expanded={open}
                aria-describedby={open ? id : undefined}
                onClick={() => (open ? setPosition(null) : show())}
            >
                i
            </button>
            {open &&
                createPortal(
                    <div
                        id={id}
                        role="tooltip"
                        className="description-info-tooltip"
                        style={position}
                    >
                        {description}
                    </div>,
                    document.body,
                )}
        </div>
    );
}
