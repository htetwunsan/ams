import React, { useContext } from 'react';
import clsx from 'clsx';
import { HomeContext } from '../pages/HomePage';

export default function MoviePoster({ movie, wrapperClasses, iconClasses, children }) {

    const context = useContext(HomeContext);

    return (
        <button className={clsx("flex items-center justify-center relative group", wrapperClasses)}
            onClick={e => context.setSelectedMovie(movie)}>
            <span className={clsx("absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2 z-10 opacity-0 material-icons-outlined group-hover:text-sky-400 group-hover:opacity-100 duration-200", iconClasses)}>
                play_arrow
            </span>
            <img className="w-full h-full object-cover group-hover:opacity-50 duration-200" src={movie.image_src} alt={movie.image_alt} />
            {children}
        </button>
    );
}
