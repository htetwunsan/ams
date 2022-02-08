import React, { useState, useRef, useEffect } from 'react';
import repository from '../repository';
import MoviePoster from './MoviePoster';

export default function TopMovieCarousel() {

    const refs = useRef([]);
    const carouselRef = useRef(null);
    const nextRef = useRef(null);
    const previousRef = useRef(null);

    const hoverRef = useRef(false);

    const [movies, setMovies] = useState([]);

    useEffect(() => {
        const controller = new AbortController();
        repository.getPopular(controller).then(response => {
            setMovies(response.data?.data ?? []);
            toggleButtons();
        }).catch(reason => console.log(reason));
        return () => {
            controller.abort();
        };
    }, []);

    const calculateIntervalAction = () => {
        if (nextRef.current?.classList.contains('hidden')) {
            return false;
        }
        return true;
    };

    const handleOnScroll = e => {
        toggleButtons();
    };

    const handleOnClickPrevious = e => {
        if (refs.current && carouselRef.current) {
            const el = carouselRef.current;
            el.scroll(el.scrollLeft - refs.current[0].clientWidth, 0);
        }
    };

    const handleOnClickNext = e => {
        if (refs.current && carouselRef.current) {
            const el = carouselRef.current;
            el.scroll(el.scrollLeft + refs.current[0].clientWidth, 0);
        }
    };

    const toggleButtons = () => {
        const childWidth = refs.current[0]?.clientWidth ?? 0;
        const carouselWidth = carouselRef.current?.clientWidth ?? 0;
        const carouselScrollLeft = carouselRef.current?.scrollLeft ?? 0;
        const carouselScrollWidth = carouselRef.current?.scrollWidth ?? 0;

        if (carouselScrollLeft >= childWidth) {
            previousRef.current?.classList.remove('hidden');
        } else {
            previousRef.current?.classList.add('hidden');
        }

        if (carouselScrollWidth - (carouselScrollLeft + carouselWidth) > 0) {
            nextRef.current?.classList.remove('hidden');
        } else {
            nextRef.current?.classList.add('hidden');
        }
    };


    useEffect(() => {
        const interval = setInterval(() => {
            if (hoverRef.current) return;
            if (calculateIntervalAction()) {
                handleOnClickNext();
            } else {
                carouselRef.current?.scroll(0, 0);
            }
        }, 7000);
        return () => {
            clearInterval(interval);
        };
    }, []);

    return (
        <div className="flex flex-col items-stretch my-2">
            {movies &&
                <h2 className="text-base italic font-light border-l-4 border-sky-400 pl-2 ml-2 mb-1">Popular</h2>
            }
            <div className="flex flex-col items-stretch min-h-[50px] relative"
                onMouseEnter={e => hoverRef.current = true}
                onMouseLeave={e => hoverRef.current = false}>
                <ul ref={carouselRef}
                    className="list-none flex gap-x-2 mx-2 overflow-y-auto no-scrollbar scroll-smooth snap-x snap-mandatory"
                    style={{ overflowScrolling: "touch" }}
                    onScroll={handleOnScroll}>
                    {
                        movies?.map(
                            (movie, index) =>
                                <li ref={ref => refs.current[index] = ref} className="basis-1/6 lg:basis-[12%] flex-none flex flex-col items-stretch snap-start" key={movie.slug}>
                                    <MoviePoster movie={movie} wrapperClasses="group" iconClasses="text-3xl" children={
                                        <div className="absolute top-1 left-1 opacity-0 group-hover:opacity-100 duration-200 flex flexc-l items-stretch">
                                            <h1 className="text-2xs md:text-xs lg:text-sm text-center font-bold leading-none line-clamp-3">{movie.name}</h1>
                                        </div>
                                    } />
                                </li>
                        )
                    }
                </ul>
                {
                    movies &&
                    <button ref={previousRef} className="absolute left-1 top-1/2 -translate-y-1/2" onClick={handleOnClickPrevious}>
                        <span className="bg-slate-400/30 material-icons-outlined rounded-full hover:text-sky-400 hover:bg-slate-900">
                            keyboard_arrow_left
                        </span>
                    </button>
                }
                {
                    movies &&
                    <button ref={nextRef} className="absolute right-1 top-1/2 -translate-y-1/2" onClick={handleOnClickNext}>
                        <span className="bg-slate-400/25 material-icons-outlined rounded-full hover:text-sky-400 hover:bg-slate-900">
                            keyboard_arrow_right
                        </span>
                    </button>
                }
            </div>
        </div>
    );
}
