import clsx from 'clsx';
import React, { useEffect, useState, useContext } from 'react';
import { HomeContext } from '../pages/HomePage';
import repository from '../repository';
import MoviePoster from './MoviePoster';

export default function MovieItem({ movie }) {

    const [detail, setDetail] = useState(null);
    const context = useContext(HomeContext);

    useEffect(() => {
        const controller = new AbortController();
        repository.getDetail(movie.slug, controller).then(response => {
            setDetail(response.data);
        }).catch(reason => console.log(reason));

        return () => {
            controller.abort();
        };
    }, [movie]);

    return (
        <div className="flex py-2 max-h-64 md:max-h-72 lg:max-h-min">
            <MoviePoster movie={movie} wrapperClasses="flex-none basis-1/3" iconClasses="text-5xl" />
            <article className="flex-grow flex flex-col items-stretch gap-y-1 px-2">
                <button className="text-base text-left font-semibold line-clamp-2 hover:text-sky-400 duration-200"
                    onClick={e => context.setSelectedMovie(movie)}>
                    {movie.name}
                    {/* {detail ? detail.video_title : 'Loading...'} */}
                </button>

                <div className="flex-grow flex flex-col items-stretch my-2">
                    <blockquote className={clsx("text-sm italic tracking-wide border-l-4 border-sky-400 pl-4", "line-clamp-4 sm:line-clamp-5 md:line-clamp-7 lg:line-clamp-9 xl:line-clamp-11 2xl:line-clamp-13")}>
                        {detail ? detail.video_description : 'Loading...'}
                    </blockquote>

                    <div className="flex mt-4">
                        <button className="flex items-center gap-x-1 group"
                            onClick={e => context.setSelectedMovie(movie)}>
                            <span className="material-icons-outlined group-hover:text-sky-400 duration-200">
                                slideshow
                            </span>
                            <span className="text-sm">Watch now</span>
                        </button>
                    </div>
                </div>

                <div className="flex justify-between leading-none mb-2">
                    {/* <div className="flex items-center justify-center gap-x-1">
                        <span className="text-sm material-icons-outlined">
                            favorite
                        </span>
                        <span className="text-sm">
                            100 likes
                        </span>
                    </div> */}
                    <h6 className="text-sm italic">
                        {detail?.related_episodes?.length ?? 0} {(detail?.related_episodes?.length ?? 0) === 1 ? "Episode" : "Episodes"}
                    </h6>
                    <time className="text-sm font-light italic">{movie.original_date}</time>
                </div>
            </article>
        </div>
    );
}
