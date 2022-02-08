import React from 'react';
import repository from '../repository';
import clsx from 'clsx';

export default function Pagination({ filter, paginator, setPaginator }) {

    const handleClick = (e, url) => {
        setPaginator(null);
        window.scrollTo(0, 0);
        repository.get(url).then(response => {
            setPaginator(response.data);
        }).catch(reason => {
            console.log(reason);
        });
    };

    return (
        <div className="flex flex-col items-stretch">
            <div className="flex">
                {
                    paginator?.previous_page_url &&
                    <button className="bg-slate-800 flex items-center justify-center w-6 h-6 rounded hover:w-7 hover:h-7 duration-200"
                        onClick={e => handleClick(e, paginator.previous_page_url)}>
                        <span className="text-base text-slate-100 hover:text-sky-400 leading-none material-icons-outlined">
                            keyboard_arrow_left
                        </span>
                    </button>
                }
                <ul className="list-none flex-grow flex items-center justify-center gap-x-1">
                    {
                        paginator?.more_urls.map(url => (
                            <li className="flex flex-col items-stretch" key={url}>
                                <button className="bg-slate-800 flex items-center justify-center w-6 h-6 rounded hover:w-7 hover:h-7 duration-200"
                                    onClick={e => handleClick(e, url)}>
                                    <span className={clsx("text-sm hover:text-sky-400 leading-none", url === paginator.active_url ? "text-sky-400" : "text-slate-100")}>
                                        {new URLSearchParams(url.split('?')[1]).get('page')}
                                    </span>
                                </button>
                            </li>
                        ))
                    }
                </ul>
                {
                    paginator?.next_page_url &&
                    <button className="bg-slate-800 flex items-center justify-center w-6 h-6 rounded hover:w-7 hover:h-7 duration-200"
                        onClick={e => handleClick(e, paginator.next_page_url)}>
                        <span className="text-base text-slate-100 hover:text-sky-400 leading-none material-icons-outlined">
                            keyboard_arrow_right
                        </span>
                    </button>
                }
            </div>
        </div>
    );
}
