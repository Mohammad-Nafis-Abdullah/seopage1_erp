import React from 'react'
import CustomModal from '../../components/CustomModal'
import Button from '../../components/Button';
import { User } from '../../../utils/user-details';
import dayjs from 'dayjs'; 
import FileUploader from '../../../file-upload/FileUploader';

const SubmitionView = ({isOpen, close, toggle, data}) => {
    const user = data && data.user ? new User(data?.user) : null;
    
  return (
    <CustomModal
        isOpen={isOpen}
        toggleRef={toggle} 
    >
        <div className='sp1-subtask-form --modal-panel'>
            <div className='sp1-subtask-form --modal-panel-header'> 
                <div className='d-flex align-items-center'>
                    <h6>Submitted Task </h6>
                    {true && <div 
                        className="spinner-border text-dark ml-2" 
                        role="status"
                        style={{
                            width: '16px',
                            height: '16px',
                            border: '0.14em solid rgba(0, 0, 0, .25)',
                            borderRightColor: 'transparent' 
                        }}
                    />}
                </div> 
                <Button
                    aria-label="close-modal"
                    className='_close-modal'
                    onClick={close}
                >
                    <i className="fa-solid fa-xmark" />
                </Button> 
            </div>

            <div className="sp1-subtask-form --modal-panel-body">
                <div className='mt-3 d-flex flex-column' style={{gap: '10px'}}>
                    <div>
                        <span className='fs-14 font-weight-bold mb-2' style={{color: '#767581'}}>Submitted By</span>
                        <div className='d-flex align-items-center'>
                           <div>
                                <img
                                    src={user?.getAvatar()}
                                    alt={user?.getName()}
                                    width={32} 
                                    height={32}
                                    className='rounded-circle'
                                />
                            </div> 
                            <div className='d-flex flex-column px-2'>
                                <a 
                                    className='text-underline text-primary' 
                                    href={user?.getUserLink()} 
                                    style={{color: '#767581'}}
                                > 
                                    {user?.getName()} 
                                </a>
                                <span className='d-block' style={{color: '#767581'}}>
                                    {dayjs(data?.crated_at).format('MMM DD, YYYY')} at 
                                    {dayjs(data?.crated_at).format('hh:mm a')}
                                </span>
                            </div>
                        </div>
                    </div>

                  <div className="d-flex flex-column mt-3" style={{gap: '10px'}}>
                    <span className='d-block fs-14 font-weight-bold' style={{color: '#767581'}}>Links</span>
                    <ul style={{listStyle: 'unset', marginLeft: '30px'}}>
                    {
                        data?.link?.map((link,i) => (
                           <li style={{listStyle: 'unset'}} key={link + i}>
                             <a className='hover-underline text-primary' target='_blank' href={link}>
                                {link}
                              </a>
                           </li>
                        ))
                    }
                    </ul>
                  </div>

                  <div className='mt-2 mt-3'>
                    <span className='d-block fs-12 font-weight-bold mb-2' style={{color: '#767581'}}>Description</span>
                    <div className='sp1_ck_content' dangerouslySetInnerHTML={{__html: data?.text}} />
                  </div>

                  {
                    data?.attach && (
                        <div className='mt-3'>
                            <span className='d-block fs-12 font-weight-bold mb-2' style={{color: '#767581'}}>Attached Files</span>
                            <FileUploader>
                                {data?.attach?.map((file) =>(
                                    <FileUploader.Preview
                                        key={file?.name}
                                        fileName={file?.name} 
                                        downloadAble={true}
                                        deleteAble={false}
                                        downloadUrl={file?.url}
                                        previewUrl={file?.url}
                                        fileType={file?.type}
                                        ext=''
                                    />
                                ))}
                            </FileUploader>
                        </div>
                    )
                  }

                </div>      
            </div>
        </div>
    </CustomModal>
  )
}

export default SubmitionView 