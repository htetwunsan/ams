import React, { useEffect, useState } from 'react';
import repository from '../repository';
import MoviePoster from './MoviePoster';

export default function MainRightContent({ filter }) {

    const [movies, setMovies] = useState([]);

    useEffect(() => {
        const controller = new AbortController();
        repository.getRandom(controller).then(response => {
            setMovies(response.data);
        }).catch(reason => console.log(reason));
        return () => {
            controller.abort();
        };
    }, []);

    return (
        <div className="flex flex-col items-stretch">
            {movies &&
                <h2 className="text-base italic font-light border-l-4 border-sky-400 pl-2 ml-2 mb-1">Random</h2>
            }
            <ul className="list-none grid grid-cols-1 md:grid-cols-2 gap-1 px-2 py-1">
                {
                    movies.map((movie, index) => (
                        <li className="flex flex-col items-stretch" key={movie.slug}>
                            <MoviePoster movie={movie} wrapperClasses="flex-grow group" children={
                                <div className="absolute top-1 left-1 opacity-0 group-hover:opacity-100 duration-200 flex flexc-l items-stretch">
                                    <h1 className="text-2xs md:text-xs lg:text-sm text-center font-bold leading-none line-clamp-3">{movie.name}</h1>
                                </div>
                            } />
                        </li>
                    ))
                }
            </ul>
        </div>
    );
}
