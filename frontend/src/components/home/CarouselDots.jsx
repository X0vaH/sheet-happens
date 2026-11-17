function CarouselDots({ currentIndex, setCurrentIndex, maxCharacters }) {
    return (
        <div className="carousel-dots">
            {Array.from({ length: maxCharacters }, (_, index) => (
                <button
                    key={index}
                    className={`carousel-dot ${index === currentIndex ? 'carousel-dot-active' : ''}`}
                    onClick={() => setCurrentIndex(index)}
                    aria-label={`Aller au personnage ${index + 1}`}
                />
            ))}
        </div>
    );
}

export default CarouselDots;
