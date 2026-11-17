import { useRef, useState } from 'react';
import CarouselArrowButton from './CarouselArrowButton.jsx';
import CarouselDots from './CarouselDots.jsx';
import CarouselTrack from './CarouselTrack.jsx';
import { MAX_CHARACTERS } from '../../configs/characterLimit.js';

const SWIPE_THRESHOLD = 50;

function CharacterCard({ characters }) {
    const [currentIndex, setCurrentIndex] = useState(2);
    const touchStartX = useRef(null);

    const goToPrevious = () => {
        setCurrentIndex((index) =>
            index === 0 ? MAX_CHARACTERS - 1 : index - 1,
        );
    };

    const goToNext = () => {
        setCurrentIndex((index) =>
            index === MAX_CHARACTERS - 1 ? 0 : index + 1,
        );
    };

    const handleTouchStart = (event) => {
        touchStartX.current = event.touches[0].clientX;
    };

    const handleTouchEnd = (event) => {
        if (touchStartX.current === null) return;
        const delta = event.changedTouches[0].clientX - touchStartX.current;
        touchStartX.current = null;
        if (delta <= -SWIPE_THRESHOLD) goToNext();
        else if (delta >= SWIPE_THRESHOLD) goToPrevious();
    };

    return (
        <section className="character-carousel-section">
            <div className="character-count-badge">
                Mes personnages ({characters.length})
            </div>

            <div className="carousel">
                <CarouselArrowButton
                    label="Personnage précédent"
                    onClickEvent={() => goToPrevious()}
                    direction="left"
                />

                <div className="carousel-frame">
                    <span className="character-card-number">
                        {currentIndex + 1}
                    </span>

                    <div
                        className="carousel-viewport"
                        onTouchStart={handleTouchStart}
                        onTouchEnd={handleTouchEnd}
                    >
                        <CarouselTrack
                            currentIndex={currentIndex}
                            characters={characters}
                            maxCharacters={MAX_CHARACTERS}
                        />
                    </div>
                </div>

                <CarouselArrowButton
                    label="Personnage suivant"
                    onClickEvent={() => goToNext()}
                    direction="right"
                />
            </div>

            <CarouselDots
                currentIndex={currentIndex}
                setCurrentIndex={setCurrentIndex}
                maxCharacters={MAX_CHARACTERS}
            />
        </section>
    );
}

export default CharacterCard;
