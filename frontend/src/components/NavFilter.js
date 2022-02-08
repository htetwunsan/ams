import React from 'react';
import clsx from 'clsx';

export default function NavFilter({ filter, setFilter }) {

    return (
        <nav className="flex flex-col items-stretch">
            <ul className="flex items-center text-sm leading-none">
                <li className={clsx("px-2 py-2", filter === 'sub' && "border-b border-sky-400")}>
                    <button className="flex flex-col items-stretch hover:text-sky-400 duration-200" onClick={e => setFilter('sub')}>
                        Sub
                    </button>
                </li>
                <li className={clsx("px-2 py-2", filter === 'raw' && "border-b border-sky-400")}>
                    <button className="flex flex-col items-stretch hover:text-sky-400 duration-200" onClick={e => setFilter('raw')}>
                        Raw
                    </button>
                </li><li className={clsx("px-2 py-2", filter === 'movies' && "border-b border-sky-400")}>
                    <button className="flex flex-col items-stretch hover:text-sky-400 duration-200" onClick={e => setFilter('movies')}>
                        Movies
                    </button>
                </li><li className={clsx("px-2 py-2", filter === 'k-show' && "border-b border-sky-400")}>
                    <button className="flex flex-col items-stretch hover:text-sky-400 duration-200" onClick={e => setFilter('k-show')}>
                        K-Show
                    </button>
                </li><li className={clsx("px-2 py-2", filter === 'ongoing series' && "border-b border-sky-400")}>
                    <button className="flex flex-col items-stretch hover:text-sky-400 duration-200" onClick={e => setFilter('ongoing series')}>
                        Ongoing Series
                    </button>
                </li>
            </ul>
        </nav>
    );
}
