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

    const getPageFromUrl = url => new URLSearchParams(url.split('?')[1]).get('page');

    const PageButton = ({ url, child, active = false }) => {
        if (!url) return null;
        return (
            <button
                className={
                    clsx("bg-slate-800 text-base leading-none text-slate-100 flex items-center justify-center px-3 py-2 rounded hover:scale-110 hover:text-sky-400 duration-200",
                        active ? "text-sky-400 border border-slate-700" : "text-slate-100")
                }
                onClick={e => handleClick(e, url)}>
                {child}
            </button>
        );
    };

    return (
        <div className="flex flex-col items-stretch">
            {paginator &&
                <div className="flex gap-x-1">
                    <PageButton url={paginator.previous_page_url} child={
                        <span className="text-base material-icons-outlined">
                            keyboard_arrow_left
                        </span>
                    } />
                    <PageButton url={paginator.first_page_url} child={
                        <span className="">
                            First
                        </span>
                    } />
                    <ul className="list-none flex-grow flex items-center justify-center gap-x-1">
                        {
                            paginator.more_urls.map(url => (
                                <li className="flex flex-col items-stretch" key={url}>
                                    <PageButton url={url} child={
                                        <span className="">
                                            {getPageFromUrl(url)}
                                        </span>
                                    } active={url === paginator.active_url} />
                                </li>
                            ))
                        }
                    </ul>
                    <PageButton url={paginator.last_page_url} child={
                        <span className="">
                            Last
                        </span>
                    } />
                    <PageButton url={paginator.next_page_url} child={
                        <span className="text-base material-icons-outlined">
                            keyboard_arrow_right
                        </span>
                    } />
                </div>
            }
        </div>
    );
}
