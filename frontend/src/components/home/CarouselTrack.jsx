import CarouselItemPopulated from './CarouselItemPopulated.jsx';
import CarouselItemEmpty from './CarouselItemEmpty.jsx';
import { buildSlots } from '../../utils/characterMapping.js';

function CarouselTrack({ currentIndex, characters, maxCharacters }) {
    const slots = buildSlots(characters, maxCharacters);

    return (
        <div
            className="carousel-track"
            style={{ transform: `translateX(-${currentIndex * 100}%)` }}
        >
            {slots.map((character, index) => (
                <div
                    className="carousel-slide"
                    key={index}
                    inert={index !== currentIndex}
                    aria-hidden={index !== currentIndex}
                >
                    {character ? (
                        <CarouselItemPopulated character={character} />
                    ) : (
                        <CarouselItemEmpty orderIndex={index} />
                    )}
                </div>
            ))}
        </div>
    );
}

export default CarouselTrack;
