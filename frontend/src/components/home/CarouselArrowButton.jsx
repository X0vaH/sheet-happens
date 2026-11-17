function CarouselArrowButton({ label, onClickEvent, direction }) {
    return (
        <button
            className="carousel-arrow carousel-arrow-desktop"
            onClick={onClickEvent}
            aria-label={label}
        >
            {direction === 'right' ? '\u2192' : '\u2190'}
        </button>
    );
}

export default CarouselArrowButton;
