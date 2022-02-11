import React, { useEffect, useState, useContext, useRef } from 'react';
import repository from '../repository';
import MoviePoster from './MoviePoster';
import ContentLoader from 'react-content-loader';
import { HomeContext } from '../pages/HomePage';
import clsx from 'clsx';

export default function MovieDetail({ movie }) {

    const [detail, setDetail] = useState(null);
    const context = useContext(HomeContext);
    const wrapperRef = useRef(null);

    useEffect(() => {
        setDetail(null);
        const controller = new AbortController();

        repository.getDetail(movie.slug, controller).then(response => {
            setDetail(response.data);
            wrapperRef.current?.scrollIntoView({ behavior: 'smooth' });
        }).catch(reason => console.log(reason));
        return () => {
            controller.abort();
        };
    }, [movie]);


    return (
        <div ref={wrapperRef} className="flex flex-col items-stretch">

            <h1 className="text-xl font-bold mb-2">
                {movie.name}
            </h1>

            <div className="min-h-[260px] pb-[56.25%] flex flex-col items-stretch relative mb-2">
                {!detail && <ContentLoader
                    backgroundColor={"#0f172a"}
                    style={{ width: "100%" }}>
                    <rect x="10" y="0" rx="5" ry="5" width="100" height="120" />
                    <rect x="120" y="0" rx="0" ry="0" width="150" height="20" />
                    <rect x="120" y="30" rx="0" ry="0" width="150" height="70" />
                    <rect x="120" y="110" rx="0" ry="0" width="150" height="10" />
                </ContentLoader>}
                {detail &&
                    <iframe
                        className="absolute top-0 left-0 w-full h-full"
                        src={detail?.embed}
                        title={detail?.video_title}
                        allowFullScreen={true}
                        frameBorder="0"
                        marginWidth="0"
                        marginHeight="0"
                        scrolling="no" />}
            </div>

            <article className="flex flex-col items-stretch gap-y-1 px-2">
                <button className="text-base text-left font-semibold line-clamp-2 hover:text-sky-400 duration-200"
                    onClick={e => context.setSelectedMovie(movie)}>
                    {detail ? detail.video_title : 'Loading...'}
                </button>

                <div className="flex-grow flex flex-col items-stretch my-2">
                    <blockquote className=" text-sm italic tracking-wide border-l-4 border-sky-400 pl-4">
                        {detail ? detail.video_description : 'Loading...'}
                    </blockquote>
                </div>

                <div className="flex justify-end leading-none mb-2">
                    <time className="text-sm font-light italic">{movie.original_date}</time>
                </div>
            </article>

            <div className="flex flex-col items-stretch">
                <h3 className="textbase font-semibold">List of episodes</h3>

                <ul className="list-none flex items-center gap-2 flex-wrap mt-2">
                    {
                        detail?.related_episodes.map((movie, index) => (
                            <li className="basis-[31%] sm:basis-[23%] flex-none flex flex-col items-stretch" key={movie.slug}>
                                <MoviePoster movie={movie} wrapperClasses="min-h-[56px] min-w-[40px]" iconClasses="text-4xl" children={
                                    <div className={clsx("absolute top-1 left-1 bg-slate-900 text-xs rounded py-0.5 px-px", movie.sub && "text-sky-400")}>
                                        {movie.sub ? 'SUB' : 'RAW'}
                                    </div>
                                } />
                                <h6 className="text-sm mt-px">
                                    Episode - {movie.number}
                                </h6>
                                <time className="">
                                    <time className="text-xs font-light italic leading-none">{movie.original_date}</time>
                                </time>
                            </li>
                        ))
                    }
                </ul>
            </div>
        </div>
    );
}
